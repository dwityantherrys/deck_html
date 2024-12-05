<?php

namespace App\Http\Controllers\Production;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;
use Yajra\DataTables\DataTables;
use Yajra\DataTables\Html\Builder;
use Illuminate\Support\Arr;

use App\Models\LogPrint;
use App\Models\Inventory\Inventory;
use App\Models\Inventory\InventoryWarehouse;
use App\Models\Inventory\InventoryMovement;
use App\Models\Production\GoodIssued;
use App\Models\Production\JobOrderDetail;
use App\Models\Production\Bom;

class GoodIssuedController extends Controller
{ 
    private $route = 'production/good-issued';
    private $routeView = 'production.good-issued';
    private $params = [];

    public function __construct (Builder $datatablesBuilder)
    {
      $this->model = new GoodIssued();
      $this->datatablesBuilder = $datatablesBuilder;

      $this->params['route'] = $this->route;
      $this->params['routeView'] = $this->routeView;
    }

    public function search(Request $request)
    {
    //   $where = "status = " . $this->model::STATUS_PROCESS;
      $where = "1=1";
      $response = [];

      if ($request->searchKey) {
        $where .= " and number like '%{$request->searchKey}%'";
      }

      try {
        $results = $this->model->whereRaw($where)
                    ->whereIn('status', [$this->model::STATUS_PROCESS, $this->model::STATUS_PARTIAL])
                //    ->whereNotIn('id', DB::table('good_receipts')->whereNull('deleted_at')->pluck('good_issued_id'))
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

    private function _formatGIDetails ($giDetails) 
    {
        $giAdjs = [];
        $finalGIAdjs = [];
        
        foreach ($giDetails as $giDetail) {
            $jobOrderDetail = $giDetail->job_order_detail;
                        
            unset(
                $giDetail['status'],
                $giDetail['balance'],
                $giDetail['created_at'], 
                $giDetail['updated_at'], 
                $giDetail['deleted_at']
            );
            
            if(!array_key_exists($giDetail->job_order_detail_id, $giAdjs)) {
                $giDetail = $giDetail->toArray();

                $giDetail['api_uri'] = 'raw-material';
                $giDetail['api_uri_id'] = $jobOrderDetail->item_material_id;
                $giDetail['quantity_need'] = $giDetail['quantity'];
                $giDetail['has_adjustment'] = "true";
                
                $giDetailAdj = $giDetail;
                $giDetailAdj['quantity_max'] = $giDetail['quantity_need'];
                $giDetailAdj['is_quantity_over'] = false;
                
                unset(
                    $giDetail['job_order_detail'],
                    $giDetailAdj['id'], 
                    $giDetailAdj['raw_material_id'], 
                    $giDetailAdj['good_issued_id'], 
                    $giDetailAdj['api_uri'],
                    $giDetailAdj['api_uri_id'],
                    $giDetailAdj['job_order_detail_id'],
                    $giDetailAdj['job_order_detail'],
                    $giDetailAdj['quantity_need'], 
                    $giDetailAdj['has_adjustment']
                );
                
                $giDetail['adjs'][] = $giDetailAdj;
                $giDetail['inventory_used'][] = $giDetail['inventory_warehouse_id'];

                $giAdjs[$giDetail['job_order_detail_id']] = $giDetail;
                continue;
            }
            
            if(!array_key_exists($giDetail->inventory_warehouse_id, $giAdjs[$giDetail->job_order_detail_id]['inventory_used'])) {
                $giAdjs[$giDetail->job_order_detail_id]['inventory_used'][] = $giDetail->inventory_warehouse_id;
            } 

            $giAdjs[$giDetail['job_order_detail_id']]['quantity'] = $giAdjs[$giDetail['job_order_detail_id']]['quantity']+$giDetail['quantity'];
            $giDetail['quantity_need'] = $giDetail['quantity'];
            
            $giAdjs[$giDetail->job_order_detail_id]['adjs'][] = $giDetail;
        }

        foreach ($giAdjs as $key => $value) {
            $finalGIAdjs[] = $value;
        }

        return $finalGIAdjs;
    }

    public function searchById ($id)
    {
        $result = $this->model->where('id', $id)->with([
            'good_issued_details',
            'log_print' => function ($query) {
                $query->with(['employee']);
            }
        ])->first();

        // foreach ($result->good_issued_details as $goodIssuedDetail) {
        //     $jobOrderDetail = $goodIssuedDetail->job_order_detail;

        //     $goodIssuedDetail->api_uri = 'raw-material';
        //     $goodIssuedDetail->api_uri_id = $jobOrderDetail->sales_detail->item_material_id;
        //     $goodIssuedDetail->item_material_id = $jobOrderDetail->sales_detail->item_material_id;
        //     $goodIssuedDetail->length= $jobOrderDetail->sales_detail->length;
        //     $goodIssuedDetail->sheet= $jobOrderDetail->sales_detail->sheet;
        // }
        
        $result->name = $result->number;
        $result->good_issued_detail_adjs = $this->_formatGIDetails($result->good_issued_details);
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
            $this->model::STATUS_PENDING => ['label' => 'pending', 'label-color' => 'yellow'],
            $this->model::STATUS_PROCESS => ['label' => 'process', 'label-color' => 'orange'],
            $this->model::STATUS_PARTIAL => ['label' => 'process partial', 'label-color' => 'blue'],
            $this->model::STATUS_FINISH => ['label' => 'finish', 'label-color' => 'green'],
            $this->model::STATUS_CANCEL => ['label' => 'cancel', 'label-color' => 'danger'],
        ];
        
        if ($request->ajax()) {
            return Datatables::of($this->model::with(['job_order', 'pic', 'factory']))
                        ->editColumn('status', function (GoodIssued $goodIssued) use ($status) { 
                            return '<small class="label bg-'. $status[$goodIssued->status]['label-color'] . '">' . $status[$goodIssued->status]['label'] . '</small>'; 
                        })
                        ->editColumn('date', function (GoodIssued $goodIssued) { 
                            return '<a class="has-ajax-form text-red" href="" 
                                data-toggle="modal" 
                                data-target="#ajax-form"
                                data-form-url="' . url($this->route) . '"
                                data-load="'. url($this->route . '/' . $goodIssued->id . '/ajax-form') . '">
                                ' . $goodIssued->date->format('m/d/Y') . ' - ' . $goodIssued->id . '
                                </a>'; 
                        })
                        ->addColumn('jo_number', function (GoodIssued $goodIssued) { 
                            return '<a class="text-red"
                                    target="_blank"
                                    href="'. url('/production/job-order/' . $goodIssued->job_order_id . '/edit') . '">
                                    ' . $goodIssued->job_order->number . '
                                </a>'; 
                        })
                        ->addColumn('action', function (GoodIssued $goodIssued) { 
                            return \TransAction::table($this->route, $goodIssued, null, $goodIssued->log_print);
                        })
                        ->rawColumns(['date', 'status', 'jo_number', 'action'])          
                        ->make(true);
        }

        $this->params['model'] = $this->model;
        $this->params['datatable'] = $this->datatablesBuilder
                                        ->addColumn([ 'data' => 'date', 'name' => 'date', 'title' => 'Date-No' ])
                                        ->addColumn([ 'data' => 'number', 'name' => 'number', 'title' => 'Shipping Instruction No' ])
                                        ->addColumn([ 'data' => 'jo_number', 'name' => 'jo_number', 'title' => 'Job Order No' ])
                                        ->addColumn([ 'data' => 'pic.name', 'name' => 'pic.name', 'title' => 'PIC' ])
                                        ->addColumn([ 'data' => 'factory.name', 'name' => 'factory.name', 'title' => 'Factory' ])
                                        ->addColumn([ 'data' => 'status', 'name' => 'status', 'title' => 'GI Status' ])
                                        ->addColumn([ 'data' => 'action', 'name' => 'action', 'title' => 'Action' ])
                                        ->parameters([
                                            'initComplete' => 'function() { 
                                                $.getScript("'. asset("js/utomodeck.js") .'"); 
                                                $.getScript("'. asset("js/production/issued-index.js") .'"); 
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
        $this->params['model']['number'] = \RunningNumber::generate('good_issueds', 'number', \Config::get('transactions.good_issued.code'));

        return view($this->routeView . '.create', $this->params);
    }

    private function _collectionFirst($array, $condition) 
    {
        return Arr::first($array, function ($value, $key) use ($condition){
            return $value['id'] === $condition;
        });
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
        $keepGiDetails = [];
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
            $GiDetails = $request->good_issued_details;

            $params = $request->all();
            $params['date'] = date('Y-m-d', strtotime($request->date));
            $params['status'] = $this->model::STATUS_PENDING;

            unset(
                $params['id'],
                $params['submit'], 
                $params['_token'], 
                $params['order_details'],
                $params['good_issued_details']
            );
            
            $gi = $this->model->create($params);

            if(!empty($GiDetails) && count($GiDetails) > 0) {
                foreach ($GiDetails as $key => $giDetail) { 
                    $hasAdjustment = $giDetail['has_adjustment'];
                    $adjustments = $hasAdjustment === 'true' ? $giDetail['adjs'] : [];
                    $jobOrderDetail = JobOrderDetail::find($giDetail['job_order_detail_id']);

                    if($giDetail['quantity_need'] != $giDetail['quantity']) {
                        $request->session()->flash('notif', [
                            'code' => 'danger',
                            'message' => 'jumlah qty dengan qty material use harus sama.'
                        ]);
                    } 
                    
                    unset(
                        $giDetail['quantity_need'], 
                        $giDetail['has_adjustment'],
                        $giDetail['inventory_used'],
                        $giDetail['api_uri'],
                        $giDetail['api_uri_id'],
                        $giDetail['id']
                    );

                    if($hasAdjustment) unset($giDetail['adjs']);

                    if($hasAdjustment === 'true') {    
                        foreach ($adjustments as $keyAdj => $adj) {
                            $giDetail['status'] = $this->model::STATUS_PENDING;
                            $giDetail['inventory_warehouse_id'] = $adj['inventory_warehouse_id'];
                            $giDetail['quantity'] = str_replace(',', '', $adj['quantity']);
                            $giDetail['balance'] = str_replace(',', '', $adj['quantity']);

                            $gi->good_issued_details()->create($giDetail);
                        }

                        continue;
                    }
                }
            }

            if($submitAction == 'save_print') {
                $redirectOnSuccess .= "?print=" .$gi->id;
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
        $result = $this->model->where('id', $id)->with([
            'good_issued_details',
            'log_print' => function ($query) {
                $query->with(['employee']);
            }
        ])->first();
       
        $result->good_issued_details = $this->_formatGIDetails($result->good_issued_details);

        $this->params['model'] = $result;
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
        // dd($request->all());
        $redirectOnSuccess = $this->route;
        $keepGiDetails = [];
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
            $goodIssuedDetails = $request->good_issued_details;

            $params = $request->all();
            $params['date'] = date('Y-m-d', strtotime($request->date));
            $params['status'] = $this->model::STATUS_PENDING;

            unset(
                $params['id'],
                $params['submit'], 
                $params['_token'], 
                $params['order_details'],
                $params['good_issued_details']
            );

            $goodIssued = $this->model->where('id', $id)->first();
            $goodIssued->update($params);

            if(!empty($goodIssuedDetails) && count($goodIssuedDetails) > 0) {
                foreach ($goodIssuedDetails as $key => $goodIssuedDetail) {
                    $id = $goodIssuedDetail['id'];
                    $hasAdjustment = $goodIssuedDetail['has_adjustment'];
                    $adjustments = $hasAdjustment === 'true' ? $goodIssuedDetail['adjs'] : [];
                    $jobOrderDetail = JobOrderDetail::find($goodIssuedDetail['job_order_detail_id']);

                    if($goodIssuedDetail['quantity_need'] != $goodIssuedDetail['quantity']) {
                        $request->session()->flash('notif', [
                            'code' => 'danger',
                            'message' => 'jumlah qty dengan qty material use harus sama.'
                        ]);
                    }

                    unset(
                        $goodIssuedDetail['quantity_need'], 
                        $goodIssuedDetail['has_adjustment'],
                        $goodIssuedDetail['inventory_used'],
                        $goodIssuedDetail['api_uri'],
                        $goodIssuedDetail['api_uri_id'],
                        $goodIssuedDetail['id']
                    );

                    if($hasAdjustment) unset($goodIssuedDetail['adjs']);

                    if($hasAdjustment === 'true') {    
                        foreach ($adjustments as $keyAdj => $adj) {
                            $goodIssuedDetail['status'] = $this->model::STATUS_PENDING;
                            $goodIssuedDetail['inventory_warehouse_id'] = $adj['inventory_warehouse_id'];
                            $goodIssuedDetail['quantity'] = str_replace(',', '', $goodIssuedDetail['quantity']);
                            $goodIssuedDetail['balance'] = $goodIssuedDetail['quantity'];
                            
                            $currentGiDetail = $goodIssued->good_issued_details()->where('id', $id)->first();
                            if(!empty($currentGiDetail)) {
                                $currentGiDetail->update($goodIssuedDetail);
                                $keepGiDetails[] = $currentGiDetail->id;
                                continue;
                            }

                            $newGiDetail = $goodIssued->good_issued_details()->create($goodIssuedDetail);
                            $keepGiDetails[] = $newGiDetail->id;
                        }

                        continue;
                    }
                }

                // hapus yang gk ada di request
                $goodIssued->good_issued_details()->whereNotIn('id', $keepGiDetails)->delete();
            }

            if($submitAction == 'save_print') {
                $redirectOnSuccess .= "?print=" .$goodIssued->id;
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
            $goodIssued = $this->model->find($id);
            $goodIssuedDetails = $goodIssued->good_issued_details;
            $jobOrderDetailUpdated = [];

            if($goodIssued->status === $goodIssued::STATUS_PROCESS) {
                foreach ($goodIssuedDetails as $goodIssuedDetail) {
                    // rollback stock
                    $invExistInWH = InventoryWarehouse::where('id', $goodIssuedDetail['inventory_warehouse_id'])->first();
                    $invWH['stock'] = $invExistInWH->stock + $goodIssuedDetail['quantity'];
                    $invExistInWH->update($invWH);

                    $inv['stock'] = InventoryWarehouse::where('inventory_id', $invExistInWH['inventory_id'])->sum('stock');
                    $inventory = Inventory::where('id', $invExistInWH['inventory_id'])->update($inv);

                    $inventory = Inventory::where('id', $invExistInWH['inventory_id'])->first();
                    $inventory->inventory_movements()->where([
                            'number' => $goodIssued->number,
                            'quantity' => $goodIssuedDetail['quantity'],
                            'created_at' => $goodIssued->log_print->created_at
                        ])->delete();

                    if(!in_array($goodIssuedDetail['job_order_detail_id'], $jobOrderDetailUpdated)) {
                        $jobOrderDetail = $goodIssuedDetail->job_order_detail;
                        $jobOrderDetailUpdated[] = $goodIssuedDetail['job_order_detail_id'];
    
                        $bom = Bom::where([
                                'production_category' => Bom::TYPE_CATEGORY_FINISH,
                                'item_id' => $jobOrderDetail->item_material_id
                               ])->first();
    
                        $bomDetail = $bom->bom_details()->where('material_id', $goodIssuedDetail['raw_material_id'])->first();
                        $manufactureQuantity = ($goodIssuedDetail->quantity/$bomDetail->quantity) * $bom->manufacture_quantity;
                        $afterIssuedQuantity = $jobOrderDetail->balance_issued+$manufactureQuantity;
    
                        if($afterIssuedQuantity <= $jobOrderDetail->quantity) $goodIssuedDetail->job_order_detail()->update(['balance_issued' => $afterIssuedQuantity]);
                    }
                }
            }
            
            $goodIssued->good_issued_details()->forceDelete();
            $goodIssued->log_print()->delete();
            $goodIssued->forceDelete();
            
            DB::commit();
            return response()->json([], 204);
        } catch (\Throwable $th) {
            DB::rollback();
            return response()->json(['message' => $th->getMessage()], 500);
        }
    }

    private function _updateInventory($params)
    {
        $invWH['type_movement'] = 1; //wh to wh
        $invWH['warehouse_id'] = $params['warehouse_id'];
        $invWH['stock'] = $params['quantity'];

        $invMV['number'] = $params['number'];
        $invMV['quantity'] = $params['quantity'];
        $invMV['warehouse_departure_id'] = $params['warehouse_id'];
        $invMV['warehouse_arrival_id'] = $params['factory_id']; 
        $invMV['date_departure'] = $params['date']; 
        $invMV['date_arrival'] = $params['updated_at']; 
        $invMV['status'] = InventoryMovement::MOVEMENT_FINISH;
        $invMV['is_defect'] = false;

        $invExistInWH = InventoryWarehouse::where('id', $params['inventory_warehouse_id'])->first();

        if(empty($invExistInWH)) {
            throw new \Exception("inventory warehouse tidak ditemukan");
        }else {
            if (($invExistInWH->stock - $params['quantity']) <= 0) {
                throw new \Exception("inventory warehouse number: " . $invExistInWH->inventory_warehouse->inventory_warehouse_number . " tidak cukup");
            }
            
            $invWH['stock'] = $invExistInWH->stock - $params['quantity'];
            $invExistInWH->update($invWH);
        }

        $inv['stock'] = InventoryWarehouse::where('inventory_id', $invExistInWH['inventory_id'])->sum('stock');
        $inventory = Inventory::where('id', $invExistInWH['inventory_id'])->update($inv);

        $inventory = Inventory::where('id', $invExistInWH['inventory_id'])->first();
        $inventory->inventory_movements()->create($invMV);
        return;
    }

    public function print(Request $request, $id)
    {
        $roleUser = request()->user()->role->name;
        $isSuperAdmin = $roleUser === 'super_admin';
        $jobOrderDetailUpdateds = [];

        try {
            DB::beginTransaction();
            $goodIssued = $this->model->find($id);
            $giDetails = $goodIssued->good_issued_details;
            $orderDetails = $goodIssued->job_order->job_order_details;
            $params['model'] = $goodIssued;
            
            if(!empty($goodIssued->log_print)) {
                if($isSuperAdmin) return \PrintFile::original($this->routeView . '.pdf', $params, 'Good-Issued-' . $goodIssued->number);
                //print with watermark
                return \PrintFile::copy($this->routeView . '.pdf', $params, 'Good-Issued-' . $goodIssued->number);
            }
            
            //print without watermark
            LogPrint::create([
                'transaction_code' => \Config::get('transactions.good_issued.code'),
                'transaction_number' => $goodIssued->number,
                'employee_id' => Auth()->user()->id,
                'date' => now()
            ]);

            // prepare inventory data
            $inventoryData = [
                'number' => $goodIssued->number,
                'date' => $goodIssued->date,
                'updated_at' => $goodIssued->updated_at,
                'warehouse_id' => $goodIssued->warehouse_id,
                'factory_id' => $goodIssued->factory_id
            ];

            foreach ($giDetails as $key => $giDetail) {
                $giDetailStatus = $this->model::STATUS_PROCESS;
                $rawMaterialId = $giDetail->raw_material_id;                
               
                $giDetail->update([ 'status' => $giDetailStatus ]);

                if(!array_key_exists($giDetail['job_order_detail_id'], $jobOrderDetailUpdateds)) {
                    /**
                     * jika good issued details memiliki lebih dari 1 job_order_detail_id yang sama, cukup ambil salah satu saja.
                     * karena sudah cukup untuk mencari tahu quantity job_order_detail yang di issued
                     **/
                    $jobOrderDetail = $giDetail->job_order_detail;
                    $jobOrderDetailUpdateds[$giDetail['job_order_detail_id']] = [
                        'item_id' => $jobOrderDetail->item_material_id,
                        'raw_material_id' => $giDetail['raw_material_id'],
                        'quantity' => $giDetail->quantity,
                        'balance_issued' => $jobOrderDetail->balance_issued
                    ];
                }else {
                    $jobOrderDetailUpdateds[$giDetail['job_order_detail_id']]['quantity'] = $jobOrderDetailUpdateds[$giDetail['job_order_detail_id']]['quantity']+$giDetail->quantity;
                }

                $this->_updateInventory(array_merge($inventoryData, $giDetail->toArray()));
            }

            foreach ($jobOrderDetailUpdateds as $jobOrderDetailUpdatedId => $jobOrderDetailUpdated) {
                $bom = Bom::where([
                    'production_category' => Bom::TYPE_CATEGORY_FINISH,
                    'item_id' => $jobOrderDetailUpdated['item_id']
                   ])->first();

                $bomDetail = $bom->bom_details()->where('material_id', $jobOrderDetailUpdated['raw_material_id'])->first();
                $manufactureQuantity = ($jobOrderDetailUpdated['quantity']/$bomDetail->quantity) * $bom->manufacture_quantity;
                $afterIssuedQuantity = $jobOrderDetailUpdated['balance_issued']-$manufactureQuantity;

                if($afterIssuedQuantity >= 0) JobOrderDetail::find($jobOrderDetailUpdatedId)->update(['balance_issued' => $afterIssuedQuantity]);
            }
            
            // $goodIssued->good_issued_details()->update(['status' => $this->model::RELEASE]);
            $goodIssued->status = $this->model::STATUS_PROCESS;
            $goodIssued->save();

            DB::commit();
            return \PrintFile::original($this->routeView . '.pdf', $params, 'Good-Issued-' . $goodIssued->number);
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
            'job_order_id' => ['required'],
            'warehouse_id' => ['required'],
            'factory_id' => ['required'],
            'created_by' => ['required'],
            'number' => ['required', 'unique:good_issueds,number' . $ignoredId],
            'date' => ['required'],
            'good_issued_details.*.quantity' => ['required']
        ]);
    }
}
