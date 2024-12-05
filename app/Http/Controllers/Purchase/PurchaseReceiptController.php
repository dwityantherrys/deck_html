<?php

namespace App\Http\Controllers\Purchase;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;
use Yajra\DataTables\DataTables;
use Yajra\DataTables\Html\Builder;
use Illuminate\Support\Arr;

use App\Models\LogPrint;
use App\Models\Purchase\Purchase;
use App\Models\Purchase\PurchaseReceipt;
use App\Models\Inventory\Inventory;
use App\Models\Inventory\InventoryMovement;
use App\Models\Master\Material\RawMaterial;
use App\Models\Master\Item\ItemMaterial;
use Carbon\Carbon;

class PurchaseReceiptController extends Controller
{
    private $route = 'purchase/receipt';
    private $routeView = 'purchase.receipt';
    private $params = [];

    public function __construct (Builder $datatablesBuilder)
    {
      $this->model = new PurchaseReceipt();
      $this->datatablesBuilder = $datatablesBuilder;

      $this->params['route'] = $this->route;
      $this->params['routeView'] = $this->routeView;
    }

    public function search(Request $request)
    {
      // saat purchase invoice, seharusnya number yang sudah di buatkan invoice, tidak muncul lagi.
      $where = "1=1";
      $where .= " and status <> " . PurchaseReceipt::RECEIPT_PROCESS;
      $response = [];

      if ($request->searchKey) {
        $where .= " and number like '%{$request->searchKey}%'";
      }

      try {
        $results = $this->model->whereRaw($where)
                ->whereNotIn('id', DB::table('shipping_instructions')->whereNull('deleted_at')->pluck('purchase_receipt_id'))
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

    private function _formatReceiptDetails ($receiptDetails, $format = null)
    {
        $receiptAdjs = [];
        $finalReceiptAdjs = [];

        foreach ($receiptDetails as $receiptDetail) {
            // dd($receiptDetail);
          // $receiptDetail->raw_material_id = $receiptDetail->purchase_detail->raw_material_id;
            $receiptDetail->item_material_id = $receiptDetail->purchase_detail->item_material_id;

            unset(
                $receiptDetail['status'],
                $receiptDetail['purchase_receive_id'],
                $receiptDetail['estimation_price'],
                $receiptDetail['amount'],
                $receiptDetail['created_at'],
                $receiptDetail['updated_at'],
                $receiptDetail['deleted_at'],
                $receiptDetail['purchase_detail']
            );

            if(!array_key_exists($receiptDetail->purchase_detail_id, $receiptAdjs)) {
                $hasAdjustment = ($receiptDetail->purchase_detail->receipt_details()->count() > 1);

                $purchaseDetail = $receiptDetail->purchase_detail->toArray();
                if($format == 'invoice-format') {
                    // $rawMaterial = $receiptDetail->purchase_detail->raw_material;
                    // $purchaseDetail['item_name'] = $rawMaterial->name . ' ' . $rawMaterial->material->name . ' ' . $rawMaterial->thick . 'mm ' .$rawMaterial->color->name;

                    $itemMaterial = $receiptDetail->purchase_detail->item_material;
                    $purchaseDetail['item_name'] = $itemMaterial->name . ' ' . $itemMaterial->material->name . ' ' . $itemMaterial->thick . 'mm ' .$itemMaterial->color->name;
                }

                $purchaseDetail['purchase_detail_id'] = $receiptDetail['purchase_detail_id'];
                $purchaseDetail['quantity'] = $receiptDetail['quantity'];
                $purchaseDetail['quantity_max'] = $purchaseDetail['quantity_left'];
                $purchaseDetail['has_adjustment'] = $hasAdjustment;

                unset(
                    $purchaseDetail['request_status'],
                    $purchaseDetail['order_status'],
                    $purchaseDetail['purchase_id'],
                    $purchaseDetail['created_at'],
                    $purchaseDetail['updated_at'],
                    $purchaseDetail['deleted_at'],
                    $receiptDetail['status'],
                    $receiptDetail['purchase_receive_id'],
                    $receiptDetail['estimation_price'],
                    $receiptDetail['amount'],
                    $receiptDetail['created_at'],
                    $receiptDetail['updated_at'],
                    $receiptDetail['deleted_at'],
                    $receiptDetail['purchase_detail']
                );

                if($format == 'invoice-format') {
                    // $rawMaterial = $receiptDetail->purchase_detail->raw_material;
                    // $receiptDetail['item_name'] = $rawMaterial->name . ' ' . $rawMaterial->material->name . ' ' . $rawMaterial->thick . 'mm ' .$rawMaterial->color->name;

                    $itemMaterial = $receiptDetail->purchase_detail->item_material;
                    $receiptDetail['item_name'] = $itemMaterial->name . ' ' . $itemMaterial->material->name . ' ' . $itemMaterial->thick . 'mm ' .$itemMaterial->color->name;
                }
                $purchaseDetail['adjs'][] = $receiptDetail;

                $receiptAdjs[$receiptDetail->purchase_detail_id] = $purchaseDetail;
                continue;
            }

            if($format == 'invoice-format') {
                // $rawMaterial = $receiptDetail->purchase_detail->raw_material;
                // $receiptDetail['item_name'] = $rawMaterial->name . ' ' . $rawMaterial->material->name . ' ' . $rawMaterial->thick . 'mm ' .$rawMaterial->color->name;

                $itemMaterial = $receiptDetail->purchase_detail->item_material;
                $receiptDetail['item_name'] = $itemMaterial->name . ' ' . $itemMaterial->material->name . ' ' . $itemMaterial->thick . 'mm ' .$itemMaterial->color->name;
            }

            $receiptAdjs[$receiptDetail->purchase_detail_id]['quantity'] = $receiptDetail->purchase_detail->receipt_details()->sum('quantity');
            $receiptAdjs[$receiptDetail->purchase_detail_id]['adjs'][] = $receiptDetail;
        }

        foreach ($receiptAdjs as $key => $value) {
            $finalReceiptAdjs[] = $value;
        }

        return $finalReceiptAdjs;
    }

    public function searchById ($id, $format = null)
    {
        $result = $this->model->where('id', $id)->with([ 'pic',
            'purchase', 'receipt_details', 'receipt_details.purchase_detail',
            'log_print' => function ($query) {
                $query->with(['employee']);
            }
        ])
        ->first();

        $result->name = $result->number;
        $result->branch_id = $result->purchase->branch_id;
        $result->vendor_id = $result->purchase->vendor_id;
        // dd($result);
        $result->receipt_detail_adjs = $this->_formatReceiptDetails($result->receipt_details, $format);

        return response()->json($result, 200);
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        $requestStatus = [
            $this->model::RECEIPT_PROCESS => ['label' => 'receive partial', 'label-color' => 'yellow'],
            $this->model::RECEIPT_PARTIAL => ['label' => 'receive all', 'label-color' => 'green'],
            $this->model::RECEIPT_FULL => ['label' => 'receive all', 'label-color' => 'green']
        ];

        if ($request->ajax()) {
            return Datatables::of($this->model::with(['receipt_details', 'pic', 'purchase.vendor']))
                        ->addColumn('total_item', function (PurchaseReceipt $receipt) {
                            return $receipt->receipt_details->count();
                        })
                        ->editColumn('total_price', function (PurchaseReceipt $receipt) {
                            return \Rupiah::format($receipt->total_price);
                        })
                        ->editColumn('status', function (PurchaseReceipt $receipt) use ($requestStatus) {
                            return '<small class="label bg-'. $requestStatus[$receipt->status]['label-color'] . '">' . $requestStatus[$receipt->status]['label'] . '</small>';
                        })
                        ->editColumn('date', function (PurchaseReceipt $receipt) {
                            return '<a class="has-ajax-form text-red" href=""
                                data-toggle="modal"
                                data-target="#ajax-form"
                                data-form-url="' . url($this->route) . '"
                                data-load="'. url($this->route . '/' . $receipt->id . '/ajax-form') . '">
                                ' . $receipt->date->format('m/d/Y') . ' - ' . $receipt->id . '
                                </a>';
                        })
                        ->addColumn('action', function (PurchaseReceipt $receipt) {
                            return \TransAction::table($this->route, $receipt, null, $receipt->log_print);
                        })
                        ->rawColumns(['date', 'status', 'action'])
                        ->make(true);
        }

        $this->params['model'] = $this->model;
        $this->params['datatable'] = $this->datatablesBuilder
                                        ->addColumn([ 'data' => 'date', 'name' => 'date', 'title' => 'Date-No' ])
                                        ->addColumn([ 'data' => 'number', 'name' => 'number', 'title' => 'Purchase Receipt No' ])
                                        ->addColumn([ 'data' => 'pic.name', 'name' => 'pic.name', 'title' => 'PIC' ])
                                        ->addColumn([ 'data' => 'total_item', 'name' => 'total_item', 'title' => 'Item' ])
                                        ->addColumn([ 'data' => 'total_price', 'name' => 'total_price', 'title' => 'Total Amount' ])
                                        ->addColumn([ 'data' => 'status', 'name' => 'status', 'title' => 'Progress Status' ])
                                        ->addColumn([ 'data' => 'action', 'name' => 'action', 'title' => 'Action' ])
                                        ->parameters([
                                            'initComplete' => 'function() {
                                                $.getScript("'. asset("js/utomodeck.js") .'");
                                                $.getScript("'. asset("js/purchase/receipt-index.js") .'");
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
        $this->params['model']['number'] = \RunningNumber::generate('purchase_receives', 'number', \Config::get('transactions.purchase_receipt.code'));

        return view($this->routeView . '.create', $this->params);
    }

    private function _collectionFirst($array, $condition)
    {
        return Arr::first($array, function ($value, $key) use ($condition){
            return $value['id'] === $condition;
        });
    }

    private function _updateInventory($params, $reference_id, $purchase_detail_id = null)
    {
      // $rawMaterial = RawMaterial::find($reference_id);
        $itemMaterial = ItemMaterial::find($reference_id);

        // definition parameter
        $inv['type_inventory'] = Inventory::TYPE_INVENTORY_RAW;
        $inv['reference_id'] = $reference_id;
        $inv['cost_of_good'] = $params['estimation_price'];
        $inv['stock'] = $params['quantity'];

        $invWH['branch_id'] = $params['branch_id'];
        $invWH['receipt_detail_id'] = $params['id'];
        // $invWH['selling_price'] = 0;
        $invWH['stock'] = $params['quantity'];

        $invMV['number'] = $params['receipt_number'];
        $invMV['quantity'] = $params['quantity'];
        // $invMV['warehouse_departure_id'] = $params['branch_id'];
        $invMV['warehouse_arrival_id'] = $params['branch_id'];
        $invMV['date_departure'] = $params['order_date'];
        $invMV['date_arrival'] = $params['date'];
        $invMV['status'] = InventoryMovement::MOVEMENT_FINISH;
        $invMV['is_defect'] = false;

        $inventory = Inventory::where([
                            'type_inventory' => Inventory::TYPE_INVENTORY_RAW,
                            'reference_id' => $reference_id
                        ])->first();

        if (empty($inventory)) {
            $inventory = Inventory::create($inv);
            $inventoryWarehouse = $inventory->inventory_warehouses()->create($invWH);
            // $inventoryWarehouse->inventory_warehouse_number = $rawMaterial->number . '-' . $purchase_detail_id . '-' . $inventoryWarehouse->id;
            $inventoryWarehouse->inventory_warehouse_number = $itemMaterial->id . '-' . $purchase_detail_id . '-' . $inventoryWarehouse->id;
            $inventoryWarehouse->save();
        }else {
            $invExistInWH = $inventory->inventory_warehouses()
                                    ->where('branch_id', $invWH['branch_id'])
                                    ->where('receipt_detail_id', $invWH['receipt_detail_id'])
                                    ->first();

            if(empty($invExistInWH)) {
               $inventoryWarehouse = $inventory->inventory_warehouses()->create($invWH);
               // $inventoryWarehouse->inventory_warehouse_number = $rawMaterial->number . '-' . $purchase_detail_id . '-' . $inventoryWarehouse->id;
               $inventoryWarehouse->inventory_warehouse_number = $itemMaterial->id . '-' . $purchase_detail_id . '-' . $inventoryWarehouse->id;
               $inventoryWarehouse->save();
            }else {
               $invWH['stock'] = $invExistInWH->stock + $invWH['stock'];
               $invExistInWH->update($invWH);
            }

            $inv['stock'] = $inventory->inventory_warehouses()->sum('stock');
            $inventory->update($inv);
        }

        $inventory->inventory_movements()->create($invMV);
        return;
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
            $orderDetails = $request->order_details;
            $receiptDetails = $request->receipt_details;

            $params = [];
            $params['purchase_id'] = $request->purchase_id;
            $params['date'] = date('Y-m-d', strtotime($request->date));
            $params['number'] = $request->number;
            $params['status'] = $this->model::RECEIPT_PROCESS;
            $params['receive_by'] = $request->receive_by;
            $params['total_price'] = $request->total_price;

            $receipt = $this->model::create($params);

            if(!empty($receiptDetails) && count($receiptDetails) > 0) {
                foreach ($receiptDetails as $key => $receiptDetail) {
                    $hasAdjustment = $receiptDetail['has_adjustment'];
                    $adjustments = $hasAdjustment === 'true' ? $receiptDetail['adjs'] : [];

                    unset($receiptDetail['has_adjustment'], $receiptDetail['id']);
                    if($hasAdjustment) unset($receiptDetail['adjs']);

                    $receiptDetail['status'] = $this->model::RECEIPT_PROCESS;
                    $receiptDetail['quantity'] = str_replace(',', '', $receiptDetail['quantity']);
                    $receiptDetail['estimation_price'] = str_replace(',', '', $receiptDetail['estimation_price']);
                    $receiptDetail['amount'] = str_replace(',', '', $receiptDetail['amount']);

                    // loop adjs (adjustment) if exist
                    if($hasAdjustment === 'true') {
                        foreach ($adjustments as $keyAdj => $adj) {
                            $receiptDetail['status'] = $this->model::RECEIPT_PROCESS;
                            $receiptDetail['quantity'] = str_replace(',', '', $adj['quantity']);

                            $receiptDetailData = $receipt->receipt_details()->create($receiptDetail);
                        }

                        continue;
                    }

                    $receiptDetailData = $receipt->receipt_details()->create($receiptDetail);
                }
            }

            DB::commit();

            $request->session()->flash('notif', [
                'code' => 'success',
                'message' => str_replace(".", " ", $this->routeView) . ' success ' . __FUNCTION__ . 'd',
            ]);

            if($submitAction == 'save_print') $redirectOnSuccess .= "?print=" . $receipt->id;

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
    public function show(Request $request, $id)
    {
        //
        $requestStatus = [
          $this->model::RECEIPT_PROCESS => ['label' => 'receive partial', 'label-color' => 'yellow'],
          $this->model::RECEIPT_PARTIAL => ['label' => 'receive all', 'label-color' => 'green'],
          $this->model::RECEIPT_FULL => ['label' => 'receive all', 'label-color' => 'green']
        ];

        if ($request->ajax()) {
          $query = $this->model::with(['receipt_details', 'pic', 'purchase.vendor']);
          if ($request->filled("periode_awal") && $request->filled("periode_akhir")) {
            $query = $query->whereBetween("date", [Carbon::parse($request->periode_awal)->startOfDay(), Carbon::parse($request->periode_akhir)->endOfDay()]);
          }
          if($request->filled("filter_pr_no")){
            $query = $query->where("number", "like", "%$request->filter_pr_no%");
          }
          if($request->filled("filter_pic")){
            $query = $query->whereHas("pic", function($q) use($request) {
              $q->where("name", "like", "%$request->filter_pic%");
            });
          }
          if($request->filled("filter_status")){
            $query = $query->where("status", $request->filter_status);
          }

          return Datatables::of($query)
          ->addColumn('total_item', function (PurchaseReceipt $receipt) {
            return $receipt->receipt_details->count();
          })
          ->editColumn('total_price', function (PurchaseReceipt $receipt) {
            return \Rupiah::format($receipt->total_price);
          })
          ->editColumn('status', function (PurchaseReceipt $receipt) use ($requestStatus) {
            return '<small class="label bg-'. $requestStatus[$receipt->status]['label-color'] . '">' . $requestStatus[$receipt->status]['label'] . '</small>';
          })
          ->editColumn('date', function (PurchaseReceipt $receipt) {
            return '<a class="has-ajax-form text-red" href=""
            data-toggle="modal"
            data-target="#ajax-form"
            data-form-url="' . url($this->route) . '"
            data-load="'. url($this->route . '/' . $receipt->id . '/ajax-form') . '">
            ' . $receipt->date->format('m/d/Y') . ' - ' . $receipt->id . '
            </a>';
          })
          ->addColumn('action', function (PurchaseReceipt $receipt) {
            return \TransAction::table($this->route, $receipt, null, $receipt->log_print);
          })
          ->rawColumns(['date', 'status', 'action'])
          ->make(true);
        }

        $this->params['model'] = $this->model;
        $this->params['datatable'] = $this->datatablesBuilder
        ->addColumn([ 'data' => 'date', 'name' => 'date', 'title' => 'Date-No' ])
        ->addColumn([ 'data' => 'number', 'name' => 'number', 'title' => 'Purchase Receipt No' ])
        ->addColumn([ 'data' => 'pic.name', 'name' => 'pic.name', 'title' => 'PIC' ])
        ->addColumn([ 'data' => 'total_item', 'name' => 'total_item', 'title' => 'Item' ])
        ->addColumn([ 'data' => 'total_price', 'name' => 'total_price', 'title' => 'Total Amount' ])
        ->addColumn([ 'data' => 'status', 'name' => 'status', 'title' => 'Progress Status' ])
        ->addColumn([ 'data' => 'action', 'name' => 'action', 'title' => 'Action' ])
        ->parameters([
          'initComplete' => 'function() {
            $.getScript("'. asset("js/utomodeck.js") .'");
            $.getScript("'. asset("js/purchase/receipt-index.js") .'");
          }',
        ]);

        return view($this->routeView . '.index', $this->params);
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
        $keepReceiptDetails = [];
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
            $orderDetails = $request->order_details;
            $receiptDetails = $request->receipt_details;

            $params = [];
            $params['date'] = date('Y-m-d', strtotime($request->date));
            $params['number'] = $request->number;
            $params['status'] = $this->model::RECEIPT_PROCESS;
            $params['receive_by'] = $request->receive_by;
            $params['total_price'] = $request->total_price;

            $receipt = $this->model->where('id', $id)->first();
            $receipt->update($params);

            if(!empty($receiptDetails) && count($receiptDetails) > 0) {
                foreach ($receiptDetails as $key => $receiptDetail) {
                    $id = $receiptDetail['id'];
                    $hasAdjustment = $receiptDetail['has_adjustment'];
                    $adjustments = $hasAdjustment === 'true' ? $receiptDetail['adjs'] : [];

                    unset($receiptDetail['has_adjustment'], $receiptDetail['id']);
                    if($hasAdjustment) unset($receiptDetail['adjs']);

                    $receiptDetail['status'] = $this->model::RECEIPT_PROCESS;
                    $receiptDetail['quantity'] = str_replace(',', '', $receiptDetail['quantity']);
                    $receiptDetail['estimation_price'] = str_replace(',', '', $receiptDetail['estimation_price']);
                    $receiptDetail['amount'] = str_replace(',', '', $receiptDetail['amount']);

                    // loop adjs (adjustment) if exist
                    if($hasAdjustment === 'true') {
                        foreach ($adjustments as $keyAdj => $adj) {
                            $id = $adj['id'];
                            $receiptDetail['status'] = $this->model::RECEIPT_PROCESS;
                            $receiptDetail['quantity'] = str_replace(',', '', $adj['quantity']);

                            $currentReceiptDetail = $receipt->receipt_details()->where('id', $id)->first();

                            if(!empty($currentReceiptDetail)) {
                                $currentReceiptDetail->update($receiptDetail);
                                $keepReceiptDetails[] = $currentReceiptDetail->id;
                                continue;
                            }

                            $newReceiptDetail = $receipt->receipt_details()->create($receiptDetail);
                            $keepReceiptDetails[] = $newReceiptDetail->id;
                        }

                        continue;
                    }

                    $currentReceiptDetail = $receipt->receipt_details()->where('id', $id)->first();

                    if(!empty($currentReceiptDetail)) {
                        $currentReceiptDetail->update($receiptDetail);
                        $keepReceiptDetails[] = $currentReceiptDetail->id;
                        continue;
                    }

                    $newReceiptDetail = $receipt->receipt_details()->create($receiptDetail);
                    $keepReceiptDetails[] = $newReceiptDetail->id;
                }

                // hapus yang gk ada di request
                $receipt->receipt_details()->whereNotIn('id', $keepReceiptDetails)->forceDelete();
            }

            if($submitAction == 'save_print') {
                $redirectOnSuccess .= "?print=" .$receipt->id;
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
    * hapus inv_warehouse (branch_id, receipt_detail_id)
    * hapus inv_movement (number ~ receipt_number, warehouse_arrival_id)
    * inventory_id, receipt_number, warehouse_arrival_id
    * update inventory (reference_id = raw_material_id)
    * update status purchase order
    * hapus receipt detail
    * hapus receipt
    */
    public function destroy($id)
    {
        try {
            DB::beginTransaction();

            $receipt = $this->model->find($id);
            // $warehouseArrivalId = $receipt->purchase->branch_id;

            // $receipt->purchase->update(['order_status' => Purchase::ORDER_PROCESS]);

            // foreach ($receipt->receipt_details as $receiptDetail) {
            //     $inventory = Inventory::where('reference_id', $receiptDetail->purchase_detail->raw_material_id)->first();

            //     $inventory->inventory_warehouses()
            //         ->where('receipt_detail_id', $receiptDetail->id)
            //         ->delete();

            //     $inventory->inventory_movements()
            //         ->where('number', $receipt->number)
            //         ->delete();

            //     $stockAfterDelete = $inventory->inventory_warehouses()->sum('stock');
            //     $inventory->stock = $stockAfterDelete;
            //     $inventory->save();

            //     $receiptDetail->purchase_detail()->update(['order_status' => Purchase::ORDER_PROCESS]);
            // }

            $receipt->receipt_details()->delete();
            $receipt->delete();

            DB::commit();
            return response()->json([], 204);
        } catch (\Throwable $th) {
            DB::rollback();
            return response()->json(['message' => $th->getMessage()], 500);
        }
    }

    public function print(Request $request, $id)
    {
        $roleUser = request()->user()->role->name;
        $isSuperAdmin = $roleUser === 'super_admin';

        try {
            DB::beginTransaction();

            $receiptStatus = $this->model::RECEIPT_FULL;

            $receipt = $this->model->where('id', $id)->with([
                'receipt_details', 'purchase','pic'])->first();
            
            $receiptDetails = $receipt->receipt_details;
            $orderDetails = $receipt->purchase->purchase_details;

            $receipt->receipt_detail_adjs = $this->_formatReceiptDetails($receiptDetails);

            $params['model'] = $receipt;

            // return json_encode($receipt);

            // if(!empty($receipt->log_print)) {
            //     if($isSuperAdmin) \PrintFile::original($this->routeView . '.pdf', $params, 'Purchase-Receipt-' . $receipt->number);
            //     //print with watermark
            //     return \PrintFile::copy($this->routeView . '.pdf', $params, 'Purchase-Receipt-' . $receipt->number);
            // }

            // //print without watermark
            // LogPrint::create([
            //     'transaction_code' => \Config::get('transactions.purchase_request.code'),
            //     'transaction_number' => $receipt->number,
            //     'employee_id' => Auth()->user()->id,
            //     'date' => now()
            // ]);

            // prepare inventory data
            $inventoryData = [
                'receipt_number' => $receipt->number,
                'date' => $receipt->date,
                'order_date' => $receipt->purchase->request_date->format('Y-m-d'),
                'branch_id' => $receipt->purchase->branch_id
            ];

            foreach ($receiptDetails as $key => $receiptDetail) {
                $inventoryData['estimation_price'] = $receiptDetail->getOriginal('estimation_price');
                $receiptDetailStatus = $this->model::RECEIPT_FULL;
                // $rawMaterialId = $receiptDetail->raw_material_id;
                $itemMaterialId = $receiptDetail->item_material_id;

                // check receipt full / receipt partial
                $purchaseOrderDetail = $this->_collectionFirst($orderDetails, $receiptDetail['purchase_detail_id']);

                if($purchaseOrderDetail['quantity_left'] > 0) {
                    $receiptStatus = $this->model::RECEIPT_PARTIAL;
                    $receiptDetailStatus = $this->model::RECEIPT_PARTIAL;
                }

                unset($receiptDetail['item_material_id']);

                $receiptDetail->update([ 'status' => $receiptDetailStatus ]);
                $receiptDetail->purchase_detail()->update(['order_status' => $receiptDetailStatus]);

                // $this->_updateInventory(array_merge($inventoryData, $receiptDetail->toArray()), $itemMaterialId, $purchaseOrderDetail['id']);
            }

            $this->model->where('id', $id)->update(['status' => $receiptStatus]);
            if($receiptStatus === $this->model::RECEIPT_FULL) $receipt->purchase()->update(['order_status' => Purchase::ORDER_FINISH]);

            DB::commit();
            // return view($this->routeView . '.pdf', $params);
            return \PrintFile::original($this->routeView . '.pdf', $params, 'Purchase-Receipt-' . $receipt->number);
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

    private function _validate ($request)
    {
        $ignoredId = !empty($request['id']) ? ','.$request['id'] : '';

        return Validator::make($request, [
            // 'sales_id' => ['required'],
            'vendor_id' => ['required'],
            'branch_id' => ['required'],
            'receive_by' => ['required'],
            'number' => ['required', 'unique:purchase_receives,number' . $ignoredId],
            'date' => ['required'],
            'receipt_details.*.quantity' => ['required'],
            'purchase_details.*.estimation_price' => ['required']
        ]);
    }
}
