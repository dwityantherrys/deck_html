<?php

namespace App\Http\Controllers\Production;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;
use Yajra\DataTables\DataTables;
use Yajra\DataTables\Html\Builder;
use Illuminate\Database\Eloquent\Builder as qBuilder;

use App\Models\LogPrint;
use App\Models\Master\Item\ItemMaterial;
use App\Models\Inventory\Inventory;
use App\Models\Inventory\InventoryWarehouse;
use App\Models\Inventory\InventoryMovement;
use App\Models\Production\Bom;
use App\Models\Production\JobOrder;
use App\Models\Production\JobOrderDetail;
use App\Models\Production\GoodIssued;
use App\Models\Production\GoodIssuedDetail;
use App\Models\Production\GoodReceipt;
use App\Models\Production\GoodReceiptDetail;
use App\Models\Production\GoodReceiptConsume;

class GoodReceiptController extends Controller
{ 
    private $route = 'production/good-receipt';
    private $routeView = 'production.good-receipt';
    private $params = [];

    public function __construct (Builder $datatablesBuilder)
    {
      $this->model = new GoodReceipt();
      $this->datatablesBuilder = $datatablesBuilder;

      $this->params['route'] = $this->route;
      $this->params['routeView'] = $this->routeView;
    }

    private function _getShippingInformation($sales)
    {
        $shipping = $sales;
        $shipping->address_id = $sales->shipping_address_id;

        if($sales->transaction_channel == $sales::TRANSACTION_CHANNEL_MOBILE) {
            $shipping = $sales->shipping_instruction;
        }

        return $shipping;
    }

    public function search(Request $request)
    {
      $where = "job_orders.type = " . JobOrder::TYPE_SALES . " and good_receipts.status != " . $this->model::STATUS_RECEIPT_PENDING;
      $response = [];

      if ($request->searchKey) {
        $where .= " and good_receipts.number like '%{$request->searchKey}%'";
      }

      try {
        $results = $this->model
                    // ->whereNotIn('id', DB::table('shipping_instructions')->whereNull('deleted_at')->pluck('good_receipt_id'))
                    ->join('good_issueds', 'good_issueds.id', 'good_receipts.good_issued_id')
                    ->join('job_orders', 'job_orders.id', 'good_issueds.job_order_id')
                    ->whereHas('good_receipt_details', function (qBuilder $query) {
                            $query->whereRaw('(good_receipt_details.quantity - (
                                select ifnull(sum(shipping_instruction_details.quantity), 0) 
                                from shipping_instruction_details
                                where shipping_instruction_details.good_receipt_detail_id = good_receipt_details.id
                                and shipping_instruction_details.deleted_at is null
                            )) > 0');
                    })
                    ->whereRaw($where)
                    ->select('good_receipts.*')
                    ->get()
                    ->makeHidden(['created_at', 'updated_at']);

        foreach ($results as $key => $result) {
            $result->name = $result->number;
        }         

        $response['results'] = $results;
      } catch (\Exception $e) {
        return response(['message' => $e->getMessage()], 500);
      }

      return response()->json($response, 200);
    }

    public function searchById ($id)
    {
        $result = $this->model->where('id', $id)->with([
            'good_issued',
            'good_receipt_details',
            'log_print' => function ($query) {
                $query->with(['employee']);
            }
        ])->first();

        foreach ($result->good_receipt_details as $receiptDetail) {
            $jobOrderDetail = $receiptDetail->job_order_detail;
            $goodIssuedDetail = $receiptDetail->job_order_detail->good_issued_details()->first();
            $itemMaterial = $jobOrderDetail->item_material;

            $bom = Bom::where([
                'production_category' => Bom::TYPE_CATEGORY_FINISH,
                'item_id' => $itemMaterial->id
               ])->first();

            $bomDetail = $bom->bom_details()->where('material_id', $goodIssuedDetail['raw_material_id'])->first();
            $manufactureQuantity = ($goodIssuedDetail->balance/$bomDetail->quantity) * $bom->manufacture_quantity;

            $receiptDetail->sales_detail_id = $jobOrderDetail->sales_id;
            $receiptDetail->item_name = $itemMaterial->item->name . ' ' . $itemMaterial->material->name . ' ' . $itemMaterial->thick . 'mm ' .$itemMaterial->color->name;
            $receiptDetail->length_formated = $jobOrderDetail->length . ' m';
            $receiptDetail->length = $jobOrderDetail->length;
            $receiptDetail->sheet = $receiptDetail->quantity/$jobOrderDetail->length;
            // $receiptDetail->price = $salesDetail->price;
            // $receiptDetail->total_price = $salesDetail->price * $receiptDetail->quantity;
            $receiptDetail->is_quantity_over = false;
        }
        
        $result->customer_id = ($result->good_issued->job_order->type == JobOrder::TYPE_SALES) ? $result->good_issued->job_order->sales->customer_id : '';
        $result->name = $result->number;

        if($result->good_issued->job_order->type == JobOrder::TYPE_SALES) {
            $result->shipping_method_id = $this->_getShippingInformation($result->good_issued->job_order->sales)->shipping_method_id;
            $result->address_id = $this->_getShippingInformation($result->good_issued->job_order->sales)->address_id;
            $result->shipping_cost = $this->_getShippingInformation($result->good_issued->job_order->sales)->shipping_cost;
        }
        
        return response()->json($result, 200);
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {        
        $status = [
            $this->model::STATUS_RECEIPT_PENDING => ['label' => 'pending', 'label-color' => 'yellow'],
            $this->model::STATUS_RECEIPT_FINISH => ['label' => 'receipt', 'label-color' => 'green']
        ];
        
        if ($request->ajax()) {
            return Datatables::of($this->model::with([
                            'good_issued', 
                            'pic', 
                            'factory'
                        ]))
                        ->editColumn('status', function (GoodReceipt $goodReceipt) use ($status) { 
                            return '<small class="label bg-'. $status[$goodReceipt->status]['label-color'] . '">' . $status[$goodReceipt->status]['label'] . '</small>'; 
                        })
                        ->editColumn('date', function (GoodReceipt $goodReceipt) { 
                            return '<a class="has-ajax-form text-red" href="" 
                                data-toggle="modal" 
                                data-target="#ajax-form"
                                data-form-url="' . url($this->route) . '"
                                data-load="'. url($this->route . '/' . $goodReceipt->id . '/ajax-form') . '">
                                ' . $goodReceipt->date->format('m/d/Y') . ' - ' . $goodReceipt->id . '
                                </a>'; 
                        })
                        ->addColumn('gi_number', function (GoodReceipt $goodReceipt) { 
                            return '<a class="text-red"
                                    target="_blank"
                                    href="'. url('/production/good-issued/' . $goodReceipt->good_issued_id . '/edit') . '">
                                    ' . $goodReceipt->good_issued->number . '
                                </a>'; 
                        })
                        ->addColumn('action', function (GoodReceipt $goodReceipt) { 
                            return \TransAction::table($this->route, $goodReceipt, null, $goodReceipt->log_print);
                        })
                        ->rawColumns(['date', 'status', 'gi_number', 'action'])          
                        ->make(true);
        }

        $this->params['model'] = $this->model;
        $this->params['datatable'] = $this->datatablesBuilder
                                        ->addColumn([ 'data' => 'date', 'name' => 'date', 'title' => 'Date-No' ])
                                        ->addColumn([ 'data' => 'number', 'name' => 'number', 'title' => 'Good Receipt No' ])
                                        ->addColumn([ 'data' => 'gi_number', 'name' => 'gi_number', 'title' => 'Good Issued No' ])
                                        ->addColumn([ 'data' => 'pic.name', 'name' => 'pic.name', 'title' => 'PIC' ])
                                        ->addColumn([ 'data' => 'factory.name', 'name' => 'factory.name', 'title' => 'Factory' ])
                                        ->addColumn([ 'data' => 'status', 'name' => 'status', 'title' => 'GR Status' ])
                                        ->addColumn([ 'data' => 'action', 'name' => 'action', 'title' => 'Action' ])
                                        ->parameters([
                                            'initComplete' => 'function() { 
                                                $.getScript("'. asset("js/utomodeck.js") .'"); 
                                                $.getScript("'. asset("js/production/receipt-index.js") .'"); 
                                            }',
                                        ]);

        return view($this->routeView . '.index', $this->params);
    }
    
    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        $this->params['model'] = $this->model;
        $this->params['model']['number'] = \RunningNumber::generate('good_receipts', 'number', \Config::get('transactions.good_receipt.code'));

        return view($this->routeView . '.create', $this->params);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $redirectOnSuccess = $this->route;
        $validator = $this->_validate($request->all());
        
        if($validator->fails())
        {
            return redirect()
                ->back()
                ->withErrors($validator)
                ->withInput();
        }

        try {
            DB::beginTransaction();
            $submitAction = $request->submit;
            $GrDetails = $request->good_receipt_details;

            $params = $request->all();
            $params['date'] = date('Y-m-d', strtotime($request->date));
            $params['status'] = $this->model::STATUS_RECEIPT_PENDING;

            unset(
                $params['id'],
                $params['submit'], 
                $params['_token'], 
                $params['order_details'],
                $params['good_issued_details']
            );

            $gr = $this->model->create($params);

            if(!empty($GrDetails) && count($GrDetails) > 0) {
                foreach ($GrDetails as $key => $grDetail) {                    
                    unset( $grDetail['id'] );

                    $grDetail['is_defect'] = GoodReceiptDetail::STATUS_NOT_DEFECT;
                    $grDetail['quantity'] = str_replace(',', '', $grDetail['quantity']);

                    $gr->good_receipt_details()->create($grDetail);
                }
            }

            if($submitAction == 'save_print') {
                $redirectOnSuccess .= "?print=" .$gr->id;
            }
            
            DB::commit();

            $request->session()->flash('notif', [
                'code' => 'success',
                'message' => str_replace(".", " ", $this->routeView) . ' success ' . __FUNCTION__ . 'd',
            ]);
            
            return redirect($redirectOnSuccess);

        } catch (\Throwable $th) {
            DB::rollback();

            $request->session()->flash('notif', [
                'code' => 'failed ' . __FUNCTION__ . 'd',
                'message' => str_replace(".", " ", $this->routeView) . ' : ' . $th->getMessage(),
            ]);

            return redirect()
                ->back()
                ->withInput();
        }
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        $this->params['model'] = $this->model->find($id);
        return view($this->routeView . '.edit', $this->params);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        $redirectOnSuccess = $this->route;
        $keepGrDetails = [];
        $validator = $this->_validate($request->all());
        
        if($validator->fails())
        {
            return redirect()
            ->back()
            ->withErrors($validator)
            ->withInput();
        }

        try {
            DB::beginTransaction();
            $submitAction = $request->submit;
            $goodReceiptDetails = $request->sales_details;

            $params = $request->all();
            $params['date'] = date('Y-m-d', strtotime($request->date));
            $params['status'] = $this->model::STATUS_RECEIPT_PENDING;

            unset(
                $params['id'],
                $params['submit'], 
                $params['_token'], 
                $params['order_details'],
                $params['good_issued_details']
            );

            $gr = $this->model->where('id', $id)->first();
            $gr->update($params);

            if(!empty($grDetails) && count($grDetails) > 0) {
                foreach ($grDetails as $key => $grDetail) {
                    $id = $grDetail['id'];

                    unset($params['id']);

                    $grDetail['is_defect'] = GoodReceiptDetail::STATUS_NOT_DEFECT;
                    $grDetail['quantity'] = str_replace(',', '', $goodIssuedDetail['quantity']);

                    $currentGrDetail = $gr->good_receipt_details()->where('id', $id)->first();

                    if(!empty($currentGrDetail)) {
                        $currentGrDetail->update($grDetail);
                        $keepGrDetails[] = $currentGrDetail->id;
                        continue;
                    }

                    $newGrDetail = $gr->good_receipt_details()->create($grDetail);
                    $keepGrDetails[] = $newGrDetail->id;
                }

                // hapus yang gk ada di request
                $gr->sales_details()->whereNotIn('id', $keepGrDetails)->delete();
            }

            if($submitAction == 'save_print') {
                $redirectOnSuccess .= "?print=" .$gr->id;
            }
            
            DB::commit();

            $request->session()->flash('notif', [
                'code' => 'success',
                'message' => str_replace(".", " ", $this->routeView) . ' success ' . __FUNCTION__ . 'd',
            ]);
            
            return redirect($redirectOnSuccess);

        } catch (\Throwable $th) {
            DB::rollback();

            $request->session()->flash('notif', [
                'code' => 'failed ' . __FUNCTION__ . 'd',
                'message' => str_replace(".", " ", $this->routeView) . ' : ' . $th->getMessage(),
            ]);

            return redirect()
                ->back()
                ->withInput();
        }
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        try {
            DB::beginTransaction();
            $gr = $this->model->find($id);
            $grDetails = $gr->good_receipt_details;
            $jobOrderDetailUpdated = [];

            if($gr->status === $gr::STATUS_RECEIPT_FINISH) {
                // return response()->json(['message' => 'tidak dapat menghapus data receipt, receipt telah di cetak'], 500);

                foreach ($grDetails as $grDetail) {
                    $quantityBeforeRollback = $grDetail->quantity;
                    //rollback job order balance
                    $rollbackReceiptQuantity = $grDetail->job_order_detail->balance+$grDetail->quantity;
                    $grDetail->job_order_detail()->update(['balance' => $rollbackReceiptQuantity]);

                    // rollback stock
                    $invExistInWH = InventoryWarehouse::where('id', $grDetail['inventory_warehouse_id'])->first();
                    $invWH['stock'] = $invExistInWH->stock - $grDetail['quantity'];
                    $invExistInWH->update($invWH);

                    $inv['stock'] = InventoryWarehouse::where('inventory_id', $invExistInWH['inventory_id'])->sum('stock');
                    $inventory = Inventory::where('id', $invExistInWH['inventory_id'])->update($inv);

                    $inventory = Inventory::where('id', $invExistInWH['inventory_id'])->first();
                    $inventory->inventory_movements()->where([
                            'number' => $gr->number,
                            'quantity' => $quantityBeforeRollback,
                            'created_at' => $gr->log_print->created_at
                        ])->delete();


                    foreach ($grDetail->good_receipt_consumes as $grConsume) {
                        $giDetail = GoodIssuedDetail::find($grConsume->good_issued_detail_id);
                        $giDetail->update(['balance' => $giDetail->balance+$grConsume->quantity]);

                        GoodReceiptConsume::find($grConsume->id)->delete();
                    }
                    // $afterReceiptQuantity = $grDetail->job_order_detail->balance-$grDetail->quantity;

                    // if(!in_array($grDetail['job_order_detail_id'], $jobOrderDetailUpdated)) {
                    //     $jobOrderDetail = $grDetail->job_order_detail;
                    //     $jobOrderDetailUpdated[] = $grDetail['job_order_detail_id'];
    
                    //     $bom = Bom::where([
                    //             'production_category' => Bom::TYPE_CATEGORY_FINISH,
                    //             'item_id' => $jobOrderDetail->sales_detail->item_material_id
                    //            ])->first();
    
                    //     $bomDetail = $bom->bom_details()->where('material_id', $grDetail['raw_material_id'])->first();
                    //     $manufactureQuantity = ($grDetail->quantity/$bomDetail->quantity) * $bom->manufacture_quantity;
                    //     $afterIssuedQuantity = $jobOrderDetail->balance_issued+$manufactureQuantity;
    
                    //     if($afterIssuedQuantity <= $jobOrderDetail->quantity) $grDetail->job_order_detail()->update(['balance_issued' => $afterIssuedQuantity]);
                    // }
                }
            }

            $gr->good_receipt_details()->forceDelete();
            $gr->log_print()->delete();
            $gr->forceDelete();
            
            DB::commit();
            return response()->json([], 204);
        } catch (\Throwable $th) {
            DB::rollback();
            return response()->json(['message' => $th->getMessage()], 500);
        }
    }

    private function _updateInventory($params, $reference_id, $job_order_detail_id = null)
    {
        $itemMaterial = ItemMaterial::find($reference_id);

        $jobOrderDetail = JobOrderDetail::find($job_order_detail_id);
        $jobOrderType = $jobOrderDetail->job_order->type;

        // definition parameter
        $inv['type_inventory'] = Inventory::TYPE_INVENTORY_FINISH; 
        $inv['reference_id'] = $reference_id;
        // $inv['cost_of_good'] = $params['estimation_price'];
        $inv['cost_of_good'] = 0;
        $inv['stock'] = $params['quantity'];
        
        $invWH['warehouse_id'] = $params['warehouse_id'];
        // $invWH['receipt_detail_id'] = $params['id'];
        // $invWH['selling_price'] = 0; 
        $invWH['stock'] = $params['quantity'];

        $invMV['number'] = $params['number'];
        $invMV['quantity'] = $params['quantity'];
        // $invMV['warehouse_departure_id'] = $params['warehouse_id'];
        $invMV['warehouse_arrival_id'] = $params['warehouse_id']; 
        $invMV['date_departure'] = $params['date']; 
        $invMV['date_arrival'] = $params['updated_at']; 
        $invMV['type_movement'] = ($jobOrderType == JobOrder::TYPE_SALES) ? InventoryMovement::TYPE_MOVEMENT_SALES : InventoryMovement::TYPE_MOVEMENT_PRODUCTION;
        $invMV['status'] = InventoryMovement::MOVEMENT_FINISH;
        $invMV['is_defect'] = false;

        $inventory = Inventory::where([
                            'type_inventory' => Inventory::TYPE_INVENTORY_FINISH,
                            'reference_id' => $reference_id
                        ])->first();

        if (empty($inventory)) {
            $inventory = Inventory::create($inv);                
            $inventoryWarehouse = $inventory->inventory_warehouses()->create($invWH);
            $inventoryWarehouse->inventory_warehouse_number = $itemMaterial->id . '-' . $job_order_detail_id . '-' . $inventoryWarehouse->id;
            $inventoryWarehouse->save();
        }else {
            $invExistInWH = $inventory->inventory_warehouses()
                                    ->where('warehouse_id', $invWH['warehouse_id'])
                                    ->where('receipt_detail_id', $params['id'])
                                    ->first();
            
            if(empty($invExistInWH)) {
               $inventoryWarehouse = $inventory->inventory_warehouses()->create($invWH);
               $inventoryWarehouse->inventory_warehouse_number = $itemMaterial->id . '-' . $job_order_detail_id . '-' . $inventoryWarehouse->id;
               $inventoryWarehouse->save(); 
            }else {
               $invWH['stock'] = $invExistInWH->stock + $invWH['stock'];
               $invExistInWH->update($invWH);
            }
    
            $inv['stock'] = $inventory->inventory_warehouses()->sum('stock');
            $inventory->update($inv);
        }

        $inventory->inventory_movements()->create($invMV);
        return $inventoryWarehouse;
    }

    public function print(Request $request, $id)
    {
        $roleUser = request()->user()->role->name;
        $isSuperAdmin = $roleUser === 'super_admin';

        try {
            DB::beginTransaction();
            $jobOrderStatus = JobOrder::STATUS_PARTIAL;
            $goodIssuedStatus = GoodIssued::STATUS_PARTIAL;
            $goodReceipt = $this->model->find($id);
            $grDetails = $goodReceipt->good_receipt_details;
            $params['model'] = $goodReceipt;
            
            if(!empty($goodReceipt->log_print)) {
                if($isSuperAdmin) return \PrintFile::original($this->routeView . '.pdf', $params, 'Good-Receipt-' . $goodReceipt->number);
                //print with watermark
                return \PrintFile::copy($this->routeView . '.pdf', $params, 'Good-Receipt-' . $goodReceipt->number);
            }
            
            //print without watermark
            LogPrint::create([
                'transaction_code' => \Config::get('transactions.good_receipt.code'),
                'transaction_number' => $goodReceipt->number,
                'employee_id' => Auth()->user()->id,
                'date' => now()
            ]);

            // prepare inventory data
            $inventoryData = [
                'number' => $goodReceipt->number,
                'date' => $goodReceipt->date,
                'updated_at' => $goodReceipt->updated_at,
                'warehouse_id' => $goodReceipt->warehouse_id,
                'factory_id' => $goodReceipt->factory_id
            ];

            foreach ($grDetails as $key => $grDetail) {
                $jobOrderDetail = $grDetail->job_order_detail;
                $itemMaterial = $jobOrderDetail->item_material;

                // $quantityIssued = $grDetail->good_receipt_consumes()->sum('quantity');
                $quantityIssued = GoodReceiptConsume::join('good_issued_details', 'good_issued_details.id', 'good_receipt_consumes.good_issued_detail_id')
                                    ->join('job_order_details', 'job_order_details.id', 'good_issued_details.job_order_detail_id')
                                    ->join('sales_details', 'sales_details.id', 'job_order_details.sales_detail_id')
                                    ->where('good_issued_details.good_issued_id', $goodReceipt->good_issued_id)
                                    ->where('sales_details.item_material_id', $itemMaterial->id)
                                    ->sum('good_receipt_consumes.quantity');
                
                // $grDetailStatus = $this->model::STATUS_RECEIPT;
                // $rawMaterialId = $grDetail->raw_material_id;                
               
                // // check receipt full / receipt partial
                // $purchaseOrderDetail = $this->_collectionFirst($orderDetails, $grDetail['job_order_detail_id']);
                // if(($purchaseOrderDetail['quantity_left'] > 0) && (($purchaseOrderDetail['quantity_left']-$grDetail['quantity']) > 0)) {
                //     $receiptStatus = $this->model::RECEIPT_PARTIAL;
                //     $grDetailStatus = $this->model::RECEIPT_PARTIAL;
                // }

                // unset($grDetail['raw_material_id']);

                // $grDetail->update([ 'status' => $grDetailStatus ]);
                // $grDetail->purchase_detail()->update(['order_status' => $grDetailStatus]);
                $afterReceiptQuantity = $jobOrderDetail->balance-$grDetail->quantity;
                $grDetail->job_order_detail()->update(['balance' => $afterReceiptQuantity]);
                if($afterReceiptQuantity <= 0) $jobOrderStatus = JobOrder::STATUS_FINISH;

                $bom = Bom::where([
                    'production_category' => Bom::TYPE_CATEGORY_FINISH,
                    'item_id' => $itemMaterial->id
                   ])->first();
    
                foreach($bom->bom_details as $bomDetail) {
                    $bomQuantityNeed = ($grDetail->quantity*$bomDetail->quantity)/$bom->manufacture_quantity;
                    // $bomQuantityNeed = $bomQuantityNeed-$quantityIssued;
                    
                    while($bomQuantityNeed > 0) {
                        $goodIssuedDetail = GoodIssuedDetail::where([
                                'good_issued_id' => $goodReceipt->good_issued_id,
                                'raw_material_id' => $bomDetail->material_id
                            ])
                            ->where('balance', '>', 0)
                            ->first();

                        $currentBalanceQuantity = $goodIssuedDetail->balance;
                        $afterIssuedQuantity = $currentBalanceQuantity-$bomQuantityNeed;
                        $balanceQuantity = ($afterIssuedQuantity <= 0) ? 0 : $afterIssuedQuantity;
                        
                        $goodIssuedDetail->update(['balance' => $balanceQuantity]);
                        $goodReceiptConsume = $goodIssuedDetail->good_receipt_consumes()->create([
                            'good_receipt_detail_id' => $grDetail->id,
                            'quantity' => ($afterIssuedQuantity >= 0) ? $bomQuantityNeed : $currentBalanceQuantity
                        ]);

                        $bomQuantityNeed = $bomQuantityNeed-$goodReceiptConsume->quantity;
                        // dd($bomQuantityNeed);
                        
                        if($balanceQuantity <= 0) $goodIssuedStatus = GoodIssued::STATUS_FINISH;
                        // dd($goodIssuedStatus);
                    }
                }
                
                $inventoryWarehouse = $this->_updateInventory(array_merge($inventoryData, $grDetail->toArray()), $grDetail['item_material_id'], $grDetail['job_order_detail_id']);
                $grDetail->update([ 'inventory_warehouse_id' => $inventoryWarehouse->id ]);
            }

            $goodReceipt->status = $this->model::STATUS_RECEIPT_FINISH;
            $goodReceipt->save();

            $goodReceipt->good_issued()->update(['status' => $goodIssuedStatus]);
            JobOrder::where('id', $goodReceipt->good_issued->job_order_id)->update(['status' => $jobOrderStatus]);

            DB::commit();
            return \PrintFile::original($this->routeView . '.pdf', $params, 'Good-Receipt-' . $goodReceipt->number);
        } catch (\Throwable $th) {
            DB::rollback();
               
            $request->session()->flash('notif', [
                'code' => 'failed ' . __FUNCTION__ . 'd',
                'message' => str_replace(".", " ", $this->routeView) . ' : ' . $th->getMessage() . $th->getLine(),
            ]);

            return redirect()
                ->back()
                ->withInput();
        }
    }

    private function _validate ($request)
    {
        $ignoredId = !empty($request['id']) ? ','.$request['id'] : '';

        return Validator::make($request, [
            'created_by' => ['required'],
            'factory_id' => ['required'],
            'warehouse_id' => ['required'],
            'number' => ['required', 'unique:good_receipts,number' . $ignoredId],
            'date' => ['required'],
            'good_receipt_details.*.job_order_detail_id' => ['required'],
            'good_receipt_details.*.item_material_id' => ['required'],
            'good_receipt_details.*.quantity' => ['required']
        ]);
    }
}
