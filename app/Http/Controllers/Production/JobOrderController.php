<?php

namespace App\Http\Controllers\Production;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;
use Yajra\DataTables\DataTables;
use Yajra\DataTables\Html\Builder;
use Illuminate\Support\Arr;
use Barryvdh\DomPDF\Facade\Pdf;

use App\Models\LogPrint;
use App\Models\Production\JobOrder;
use App\Models\Production\JobOrderDetail;
use App\Models\Production\GoodIssued;
use App\Models\Master\Item\ItemMaterial;
use App\Models\Master\Material\RawMaterial;
use App\Models\Production\Bom;
use App\Models\Master\Item\Item;

class JobOrderController extends Controller
{ 
    private $route = 'production/job-order';
    private $routeView = 'production.job-order';
    private $params = [];

    public function __construct (Builder $datatablesBuilder)
    {
      $this->model = new JobOrder();
      $this->datatablesBuilder = $datatablesBuilder;

      $this->params['route'] = $this->route;
      $this->params['routeView'] = $this->routeView;
      $this->params['joTypes'] = [
        JobOrder::TYPE_IT => ['label' => 'Peralatan IT', 'label-color' => 'grey'],
        JobOrder::TYPE_VEHICLE => ['label' => 'Kendaraan', 'label-color' => 'grey'],
        JobOrder::TYPE_AC => ['label' => 'AC', 'label-color' => 'grey'],
        JobOrder::TYPE_BUILDING => ['label' => 'Gedung / Bangunan', 'label-color' => 'grey'],
        JobOrder::TYPE_ELECTRONIC => ['label' => 'Peralatan Elektronik Gedung', 'label-color' => 'grey'],
        JobOrder::TYPE_CABLE => ['label' => 'Kabel-Kabel', 'label-color' => 'grey'],
        JobOrder::TYPE_OTHERS => ['label' => 'Lain-Lain', 'label-color' => 'grey'],
        
      ];
    }

    /* dipakai di menu shipping instruction */ 
    public function search(Request $request)
    {
      $where = "(status = " . $this->model::STATUS_PROCESS . ' or status = ' . $this->model::STATUS_PENDING . ')';
      $response = [];

      if ($request->searchKey) {
        $where .= " and number like '%{$request->searchKey}%'";
      }

      try {
        $results = $this->model->whereRaw($where)
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

    /**
     * used in menu : 
     * job order index
     */
    public function searchById ($id, $format = null)
    {
        $result = $this->model->where('id', $id)->with([
            'job_order_details',
            'log_print' => function ($query) {
                $query->with(['employee']);
            }
        ])->first();
        
        foreach ($result->job_order_details as $joDetail) {
            $joDetail->item_material_id = $joDetail->item_material_id;
            $joDetail->length_options = [];
        }
        
        $result->name = $result->number;
        return response()->json($result, 200);
    }

    private function _collectionFirst($array, $condition) 
    {
        return Arr::first($array, function ($value, $key) use ($condition){
            return $value['id'] === $condition;
        });
    }

    //tampilkan job order yang sudah di issued
    public function searchJobIssuedById ($id)
    {
        $jobOrderDetailIssueds = [];
        $resultJobOrderDetails = [];

        $goodIssued = GoodIssued::find($id);

        foreach($goodIssued->good_issued_details as $goodIssuedDetail) {
            if(!array_key_exists($goodIssuedDetail['job_order_detail_id'], $jobOrderDetailIssueds)) {
                /**
                 * jika good issued details memiliki lebih dari 1 job_order_detail_id yang sama, cukup ambil salah satu saja.
                 * karena sudah cukup untuk mencari tahu quantity job_order_detail yang di issued
                 **/
                $jobOrderDetail = $goodIssuedDetail->job_order_detail;
                $jobOrderDetailIssueds[$goodIssuedDetail['job_order_detail_id']] = [
                    'item_id' => $jobOrderDetail->item_material_id,
                    'raw_material_id' => $goodIssuedDetail['raw_material_id'],
                    'quantity' => $goodIssuedDetail->quantity,
                    'balance_issued' => $jobOrderDetail->balance_issued,
                    'gr_consume' => $goodIssuedDetail->good_receipt_consumes()->sum('quantity')
                ];
            } else{
                $jobOrderDetailIssueds[$goodIssuedDetail['job_order_detail_id']]['quantity'] = $jobOrderDetailIssueds[$goodIssuedDetail['job_order_detail_id']]['quantity']+$goodIssuedDetail->quantity;
            }
        }

        foreach ($jobOrderDetailIssueds as $jobOrderDetailIssuedId => $jobOrderDetailIssued) {
            $bom = Bom::where([
                'production_category' => Bom::TYPE_CATEGORY_FINISH,
                'item_id' => $jobOrderDetailIssued['item_id']
               ])->first();

            $bomDetail = $bom->bom_details()->where('material_id', $jobOrderDetailIssued['raw_material_id'])->first();
            $manufactureQuantity = ($jobOrderDetailIssued['quantity']/$bomDetail->quantity) * $bom->manufacture_quantity;
            
            $grConsume = $jobOrderDetailIssued['gr_consume'];
            $issuedQuantity = ($grConsume/$bomDetail->quantity) * $bom->manufacture_quantity;

            $currentJODetail = JobOrderDetail::find($jobOrderDetailIssuedId);
            $currentJODetail->item_material_id = $currentJODetail->item_material_id;
            $currentJODetail->length = $currentJODetail->length;
            $currentJODetail->sheet = $manufactureQuantity/$currentJODetail->length;
            $currentJODetail->sheet_max = $currentJODetail->sheet;
            $currentJODetail->sheet_issued = $currentJODetail->balance_issued/$currentJODetail->length;
            $currentJODetail->quantity = $manufactureQuantity;
            $currentJODetail->quantity_issued = $manufactureQuantity;
            $currentJODetail->quantity_left = $currentJODetail->quantity-$issuedQuantity;
            $currentJODetail->is_quantity_over = false;

            $resultJobOrderDetails[] = $currentJODetail;
        }
                
        $goodIssued->job_order_details = $resultJobOrderDetails;
        $goodIssued->name = $goodIssued->number;
        return response()->json($goodIssued, 200);
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {        
        $joTypes = $this->params['joTypes'];
        $joStatus = [
            $this->model::STATUS_PENDING => ['label' => 'pending', 'label-color' => 'yellow'],
            $this->model::STATUS_PROCESS => ['label' => 'on process', 'label-color' => 'orange'],
            $this->model::STATUS_PARTIAL => ['label' => 'finish partial', 'label-color' => 'blue'],
            $this->model::STATUS_FINISH => ['label' => 'finish', 'label-color' => 'green'],
            $this->model::STATUS_CANCEL => ['label' => 'cancel', 'label-color' => 'red'],
        ];
      
        if ($request->ajax()) {

            return Datatables::of(
                        $this->model::with(['job_order_details', 'pic'])
                                    ->orderBy('date', 'desc') // Tambahkan orderBy di sini
                    )
                    ->addColumn('total_item', function (JobOrder $jo) { 
                        return $jo->job_order_details->count(); 
                    })
                    ->editColumn('status', function (JobOrder $jo) use ($joStatus) { 
                        return '<small class="label bg-'. $joStatus[$jo->status]['label-color'] . '">' . $joStatus[$jo->status]['label'] . '</small>'; 
                    })
                    ->editColumn('date', function (JobOrder $jo) { 
                        $roleUser = request()->user()->role->name;
                        $isSuperAdmin = $roleUser === 'super_admin';
                    
                        if ($jo->status == $this->model::STATUS_FINISH) {
                            // Jika status adalah finish (3), tampilkan teks tanpa hyperlink
                            return '<span>' . $jo->date->format('m/d/Y') . ' - ' . $jo->id . '</span>';
                        } else {
                            // Jika status bukan finish (3), tampilkan hyperlink
                            return '<a class="has-ajax-form text-red" href="" 
                                data-toggle="modal" 
                                data-target="#ajax-form"
                                data-form-url="' . url($this->route) . '"
                                data-load="'. url($this->route . '/' . $jo->id . '/ajax-form') . '"
                                data-is-superadmin="'. $isSuperAdmin . '">
                                ' . $jo->date->format('m/d/Y') . ' - ' . $jo->id . '
                                </a>';
                        }
                    })
                    
                    ->addColumn('action', function (JobOrder $jo) { 
                        return \TransAction::table($this->route, $jo, null, $jo->log_print);
                    })
                    ->rawColumns(['date', 'sales_id', 'status', 'action'])          
                    ->make(true);
        }
        

        $this->params['model'] = $this->model;
        $this->params['datatable'] = $this->datatablesBuilder
                                        ->addColumn([ 'data' => 'date', 'name' => 'date', 'title' => 'Date-No' ])
                                        ->addColumn([ 'data' => 'number', 'name' => 'number', 'title' => 'SPK Number' ])
                                        ->addColumn([ 'data' => 'pic.name', 'name' => 'pic.name', 'title' => 'PIC' ])
                                        ->addColumn([ 'data' => 'total_item', 'name' => 'total_item', 'title' => 'Item' ])
                                        ->addColumn([ 'data' => 'status', 'name' => 'status', 'title' => 'Progress Status' ])
                                        ->addColumn([ 'data' => 'action', 'name' => 'action', 'title' => 'Action' ])
                                        ->parameters([
                                            'initComplete' => 'function() { 
                                                $.getScript("'. asset("js/utomodeck.js") .'"); 
                                                $.getScript("'. asset("js/production/order-index.js") .'"); 
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
        $this->params['model']['number'] = \RunningNumber::generate('job_orders', 'number', \Config::get('transactions.job_order.code'));

        return view($this->routeView . '.create', $this->params);
    }

    /**
     * cari id Sales, update untuk rubah status order, scenario saat ini,
     * tidak bisa insert kalau tidak input request number
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $redirectOnSuccess = $this->route;
        $keepJoDetails = [];
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

            // dd($request->all());
            $submitAction = $request->submit;
            $joDetails = $request->job_order_details;

            $params = $request->all();
            $params['date'] = date('Y-m-d', strtotime($request->date));
            $params['status'] = $this->model::STATUS_PENDING;
           
            
            if(!empty($request->due_date)) $params['due_date'] = date('Y-m-d', strtotime($request->due_date));

            unset(
                $params['id'],
                $params['submit'], 
                $params['_token'], 
                $params['order_details'],
                $params['job_order_details']
            );
            
            $jo = $this->model->create($params);

            if(!empty($joDetails) && count($joDetails) > 0) {
                foreach ($joDetails as $key => $joDetail) {
                    unset($joDetail['id']);
                    $itemMaterial = Item::find(str_replace(',', '', $joDetail['item_material_id']));
                    $itemName = $itemMaterial->name;

                    $joDetail['status'] = $this->model::STATUS_PENDING;
                    // $joDetail['item_name'] = $itemName;
                    $joDetail['price'] = str_replace(',', '', $joDetail['price']);
                    $joDetail['quantity'] = str_replace(',', '', $joDetail['quantity']);
                    $joDetail['amount'] = str_replace(',', '', $joDetail['amount']);

            
                    $jo->job_order_details()->create($joDetail);
                }
            }

            if($submitAction == 'save_print') {
                $redirectOnSuccess .= "?print=" .$jo->id;
            }
            
            DB::commit();

            $request->session()->flash('notif', [
                'code' => 'success',
                'message' => str_replace(".", " ", $this->routeView) . ' success ' . __FUNCTION__ . 'd',
            ]);
            
            return redirect($redirectOnSuccess);

        } catch (\Throwable $th) {
            DB::rollback();

            dd($th);

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
            'job_order_details',
            'log_print' => function ($query) {
                $query->with(['employee']);
            }
        ])->first();
        
        foreach ($result->job_order_details as $joDetail) {
            $quantityMax = $joDetail->sales_detail->quantity_left + $joDetail->quantity;

            $joDetail->item_material_id = $joDetail->sales_detail->item_material_id;
            $joDetail->length = $joDetail->sales_detail->length;
            $joDetail->sheet = $joDetail->quantity/$joDetail->length;
            $joDetail->sheet_max = $quantityMax/$joDetail->length;
            $joDetail->sheet_issued = $joDetail->balance_issued/$joDetail->length;
            $joDetail->quantity_max = $quantityMax;
            $joDetail->quantity_issued = $joDetail->balance_issued;
            $joDetail->is_quantity_over = false;
        }

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
    $redirectOnSuccess = $this->route;
    $keepJoDetails = [];
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
        $joDetails = $request->job_order_details;

        $params = $request->all();
        $params['date'] = date('Y-m-d', strtotime($request->date));
        $params['status'] = $this->model::STATUS_PENDING;

        if (!empty($request->due_date)) {
            $params['due_date'] = date('Y-m-d', strtotime($request->due_date));
        }

        unset($params['id'], $params['submit'], $params['_token'], $params['order_details'], $params['job_order_details']);

        $jo = $this->model->where('id', $id)->first();
        $jo->update($params);

        if (!empty($joDetails) && count($joDetails) > 0) {
            foreach ($joDetails as $key => $joDetail) {
                $id = $joDetail['id'] ?? null;
        
                // $joDetail['quantity'] = str_replace(',', '', $joDetail['quantity']);
                $joDetail['price'] = str_replace(',', '', $joDetail['price']);
                $joDetail['quantity'] = str_replace(',', '', $joDetail['quantity']);
                $joDetail['amount'] = str_replace(',', '', $joDetail['amount']);
                
                // $joDetail['balance'] = str_replace(',', '', $joDetail['quantity']);
                // $joDetail['balance_issued'] = str_replace(',', '', $joDetail['quantity']);
                // $joDetail['is_custom_length'] = isset($joDetail['is_custom_length']) 
                //     ? (($joDetail['is_custom_length'] == 'true' || $joDetail['is_custom_length'] == '1') ? 1 : 0) 
                //     : 0;
        
                unset($joDetail['id']); // Buang ID sebelum update atau create
        
                $currentJoDetail = $jo->job_order_details()->where('id', $id)->first();
        
                if (!empty($currentJoDetail)) {
                    $currentJoDetail->update($joDetail);
                    $keepJoDetails[] = $currentJoDetail->id;
                    continue;
                }
        
                $newJoDetail = $jo->job_order_details()->create($joDetail);
                $keepJoDetails[] = $newJoDetail->id;
            }
        
            // Hapus detail yang tidak ada di permintaan
            $jo->job_order_details()->whereNotIn('id', $keepJoDetails)->delete();
        }
        

        if ($submitAction == 'save_print') {
            $redirectOnSuccess .= "?print=" . $jo->id;
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
            $jo = $this->model->where('id', $id)->first();
            $jo->job_order_details()->forceDelete();
            $jo->log_print()->delete();
            $jo->forceDelete();
            
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

        $jo = $this->model->where('id', $id)->with([
            'job_order_details',
            'job_order_details.item_material',
            'pic',
            'vendor.profile',
            'vendor.profile.default_address',
            'vendor.profile.default_address.region_city',
            'vendor.profile.default_address.region_district'
        ])->first();

        $params['model'] = $jo;

        // Jika status pekerjaan bukan finish (3), maka lakukan perubahan status
        if ($jo->status != $this->model::STATUS_FINISH) {
            $jo->job_order_details()
                ->where(['status' => $this->model::STATUS_PENDING])
                ->update(['status' => $this->model::STATUS_PROCESS]);
            $jo->status = $this->model::STATUS_PROCESS;
            $jo->save();
        }

        DB::commit();

        // Generate PDF dengan ukuran kertas yang diinginkan
        $pdf = Pdf::loadView($this->routeView . '.pdf', $params)
            ->setPaper([0, 0, 419.53, 595.28]); // Ukuran setengah A4 dalam poin (A5)

        return $pdf->download('Project-' . $jo->number . '.pdf');

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
            'sales_id' => ['required_if:type,' . $this->model::TYPE_SALES],
            'created_by' => ['required'],
            'type' => ['required'],
            'number' => ['required', 'unique:job_orders,number' . $ignoredId],
            'date' => ['required'],
            'job_order_details.*.quantity' => ['required']
        ]);
    }
}