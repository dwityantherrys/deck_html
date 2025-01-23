<?php

namespace App\Http\Controllers\Purchase;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;
use Yajra\DataTables\DataTables;
use Yajra\DataTables\Html\Builder;
use Illuminate\Database\Eloquent\Builder as qBuilder;

use App\Models\LogPrint;
use App\Models\Purchase\Purchase;
use App\Models\Master\Material\RawMaterial;
use App\Models\Master\Item\Item;
use Carbon\Carbon;
use App\Helpers\PurchaseAction;


class PurchaseRequestController extends Controller
{
    private $route = 'purchase/request';
    private $routeView = 'purchase.request';
    private $params = [];

    public function __construct (Builder $datatablesBuilder)
    {
      $this->items = new Item();
      $this->model = new Purchase();
      $this->datatablesBuilder = $datatablesBuilder;

      $this->params['route'] = $this->route;
      $this->params['routeView'] = $this->routeView;
      $this->params['prTypes'] = [
        Purchase::TYPE_IT => ['label' => 'IT', 'label-color' => 'green'],
        Purchase::TYPE_HR_GA => ['label' => 'HR/GA', 'label-color' => 'green'],
        Purchase::TYPE_OPERASIONAL => ['label' => 'OPERASIONAL SALES', 'label-color' => 'green'],
        Purchase::TYPE_OFFICE_SUPPLIES => ['label' => 'PERLENGKAPAN KANTOR', 'label-color' => 'green'],
        Purchase::TYPE_VEHICLE => ['label' => 'KENDARAAN', 'label-color' => 'green'],
        Purchase::TYPE_OTHERS => ['label' => 'LAIN-LAIN', 'label-color' => 'green'],        

        ];

        $this->params['destinationTypes'] = [
            Purchase::HEAD => ['label' => 'PT CSA', 'label-color' => 'green'],
            Purchase::BRANCH => ['label' => 'KANTOR CABANG', 'label-color' => 'green']

        ];
  
    }

    // di pakai di purchase order, untuk cari purchase request yang belum di order
    public function search(Request $request)
    {
      $where = "order_status = " . $this->model::ORDER_PENDING;
      $where .= " and (order_number IS NULL and order_date IS NULL)";
      $where .= " and request_status = " . $this->model::REQUEST_ACCEPT;
      $response = [];

      if ($request->searchKey) {
        $where .= " and request_number like '%{$request->searchKey}%'";
      }

      try {
        $results = $this->model
        ->whereHas('purchase_details', function (qBuilder $query) {
            $query->whereRaw('(purchase_details.quantity - (
              select ifnull(sum(purchase_receive_details.quantity), 0)
              from purchase_receive_details
              where purchase_detail_id = purchase_details.id
              and deleted_at is null
              )) > 0');
            })
        ->whereRaw($where)
        ->get()
        ->makeHidden(['created_at', 'updated_at']);


        foreach ($results as $key => $result) {
            $result->name = $result->request_number;
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
            'purchase_details',
            'request_log_print' => function ($query) {
                $query->with(['employee']);
            }
        ])->first();

        $result->name = $result->request_number;
        return response()->json($result, 200);
    }

    public function searchShip(Request $request)
    {
      $where = "order_status = " . $this->model::ORDER_PENDING;
      $where .= " and (order_number IS NULL and order_date IS NULL)";
      $where .= " and request_status = " . $this->model::REQUEST_ACCEPT;
      $response = [];

      if ($request->searchKey) {
        $where .= " and request_number like '%{$request->searchKey}%'";
      }

      try {
        $results = $this->model
        ->whereHas('purchase_details', function (qBuilder $query) {
        $query->whereRaw('(purchase_details.quantity - (
          select ifnull(sum(shipping_instruction_details.quantity), 0)
          from shipping_instruction_details
          where purchase_detail_id = purchase_details.id
          and deleted_at is null
          )) > 0');
        })
        ->whereRaw($where)
        ->get()
        ->makeHidden(['created_at', 'updated_at']);


        foreach ($results as $key => $result) {
            $result->name = $result->request_number;
        }

        $response['results'] = $results;
      } catch (\Exception $e) {
        return response(['message' => $e->getMessage()], 500);
      }

      return response()->json($response, 200);
    }

    public function searchByIdShip ($id)
    {
        $result = $this->model->where('id', $id)->with([
            'purchase_details',
            'request_log_print' => function ($query) {
                $query->with(['employee']);
            }
        ])->first();

        $result->name = $result->request_number;
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
            $this->model::REQUEST_PENDING => ['label' => 'on request', 'label-color' => 'yellow'],
            $this->model::REQUEST_ACCEPT => ['label' => 'accept', 'label-color' => 'blue'],
            $this->model::REQUEST_REJECT => ['label' => 'reject', 'label-color' => 'red']
        ];

        if ($request->ajax()) {
            return Datatables::of($this->model::with(['purchase_details', 'pic', 'vendor']))
                        ->setRowAttr([
                            'style' => function(Purchase $purchase) {
                                if(empty($purchase->deleted_at)) return;
                                return 'background: #ffb9b9';
                            }
                        ])
                        ->addColumn('total_item', function (Purchase $purchase) {
                            return $purchase->purchase_details->count();
                        })
                        ->editColumn('total_price', function (Purchase $purchase) {
                            return \Rupiah::format($purchase->total_price);
                        })
                        ->editColumn('request_status', function (Purchase $purchase) use ($requestStatus) {
                            return '<small class="label bg-'. $requestStatus[$purchase->request_status]['label-color'] . '">' . $requestStatus[$purchase->request_status]['label'] . '</small>';
                        })
                        ->editColumn('request_date', function (Purchase $purchase) {
                            $roleUser = request()->user()->role->name;
                            $isSuperAdmin = $roleUser === 'super_admin';

                            return '<a class="has-ajax-form text-red" href=""
                                data-toggle="modal"
                                data-target="#ajax-form"
                                data-form-url="' . url($this->route) . '"
                                data-load="'. url($this->route . '/' . $purchase->id . '/ajax-form') . '"
                                data-is-superadmin="'. $isSuperAdmin . '">
                                ' . $purchase->request_date->format('m/d/Y') . ' - ' . $purchase->id . '
                                </a>';
                        })
                        ->addColumn('action', function (Purchase $purchase) {
                            return \TransAction::table($this->route, $purchase, 'order_number', $purchase->order_log_print);
                        })
                        ->rawColumns(['request_date', 'request_status', 'action'])
                        ->make(true);
        }

        $this->params['model'] = $this->model;
        $this->params['datatable'] = $this->datatablesBuilder
                                        ->addColumn([ 'data' => 'request_date', 'name' => 'request_date', 'title' => 'Date-No' ])
                                        ->addColumn([ 'data' => 'request_number', 'name' => 'request_number', 'title' => 'Purchase Request No' ])
                                        ->addColumn([ 'data' => 'pat_number', 'name' => 'pat_number', 'title' => 'PAT Number' ])
                                        ->addColumn([ 'data' => 'pic.name', 'name' => 'pic.name', 'title' => 'PIC' ])
                                        ->addColumn([ 'data' => 'total_item', 'name' => 'total_item', 'title' => 'Item' ])
                                        ->addColumn([ 'data' => 'total_price', 'name' => 'total_price', 'title' => 'Total Amount' ])
                                        ->addColumn([ 'data' => 'request_status', 'name' => 'request_status', 'title' => 'Progress Status' ])
                                        ->addColumn([ 'data' => 'action', 'name' => 'action', 'title' => 'Action' ])
                                        ->parameters([
                                            'initComplete' => 'function() {
                                                $.getScript("'. asset("js/utomodeck.js") .'");
                                                $.getScript("'. asset("js/purchase/request-index.js") .'");
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

        $this->params['model']['request_number'] = \RunningNumber::generate('purchases', 'request_number', \Config::get('transactions.purchase_request.code'));
        

        // dd($this->params);        

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
            $purchaseDetails = $request->purchase_details;

            $params = $request->all();
            // return dd($request->all());
            $params['request_date'] = date('Y-m-d', strtotime($request->request_date));
            $params['request_status'] = $this->model::REQUEST_PENDING;
            $params['order_status'] = $this->model::DEFAULT_ORDER_STATUS;

            unset($params['submit'], $params['_token'], $params['purchaseDetails']);

            $item = $this->model::create($params);

            if(!empty($purchaseDetails) && count($purchaseDetails) > 0) {
                foreach ($purchaseDetails as $key => $purchaseDetail) {
                    $items = $this->items::where('id', str_replace(',', '', $purchaseDetail['item_material_id']))->first();
                    $purchaseDetail['request_status'] = $this->model::REQUEST_PENDING;
                    $purchaseDetail['order_status'] = $this->model::DEFAULT_ORDER_STATUS;
                    $purchaseDetail['item_name'] = $items->name;
                    $purchaseDetail['quantity'] = str_replace(',', '', $purchaseDetail['quantity']);
                    $purchaseDetail['estimation_price'] = str_replace(',', '', $purchaseDetail['estimation_price']);
                    $purchaseDetail['amount'] = str_replace(',', '', $purchaseDetail['amount']);
                    $item->purchase_details()->create($purchaseDetail);
                    
                }
            }

            if($submitAction == 'save_print') {
                $redirectOnSuccess .= "?print=" .$item->id;
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
    public function show(Request $request, $id)
    {
        //
        $requestStatus = [
          $this->model::REQUEST_PENDING => ['label' => 'on request', 'label-color' => 'yellow'],
          $this->model::REQUEST_ACCEPT => ['label' => 'accept', 'label-color' => 'blue'],
          $this->model::REQUEST_REJECT => ['label' => 'reject', 'label-color' => 'red']
        ];

        if ($request->ajax()) {
          $query = $this->model::with(['purchase_details', 'pic', 'vendor']);
          if ($request->filled("periode_awal") && $request->filled("periode_akhir")) {
            $query = $query->whereBetween("request_date", [Carbon::parse($request->periode_awal)->startOfDay(), Carbon::parse($request->periode_akhir)->endOfDay()]);
          }
          if($request->filled("filter_purchase_no")){
            $query = $query->where("request_number", "like", "%$request->filter_purchase_no%");
          }
          if($request->filled("filter_pic")){
            $query = $query->whereHas("pic", function($q) use($request) {
              $q->where("name", "like", "%$request->filter_pic%");
            });
          }
          if($request->filled("filter_status")){
            $query = $query->where("request_status", $request->filter_status);
          }

          return Datatables::of($query)
          ->setRowAttr([
            'style' => function(Purchase $purchase) {
              if(empty($purchase->deleted_at)) return;
              return 'background: #ffb9b9';
            }
          ])
          ->addColumn('total_item', function (Purchase $purchase) {
            return $purchase->purchase_details->count();
          })
          ->editColumn('total_price', function (Purchase $purchase) {
            return \Rupiah::format($purchase->total_price);
          })
          ->editColumn('request_status', function (Purchase $purchase) use ($requestStatus) {
            return '<small class="label bg-'. $requestStatus[$purchase->request_status]['label-color'] . '">' . $requestStatus[$purchase->request_status]['label'] . '</small>';
          })
          ->editColumn('request_date', function (Purchase $purchase) {
            $roleUser = request()->user()->role->name;
            $isSuperAdmin = $roleUser === 'super_admin';

            return '<a class="has-ajax-form text-red" href=""
            data-toggle="modal"
            data-target="#ajax-form"
            data-form-url="' . url($this->route) . '"
            data-load="'. url($this->route . '/' . $purchase->id . '/ajax-form') . '"
            data-is-superadmin="'. $isSuperAdmin . '">
            ' . $purchase->request_date->format('m/d/Y') . ' - ' . $purchase->id . '
            </a>';
          })
          ->addColumn('action', function (Purchase $purchase) {
            return PurchaseAction::table($this->route, $purchase, 'order_number', $purchase->request_log_print);
          })
          ->rawColumns(['request_date', 'request_status', 'action'])
          ->make(true);
        }

        $this->params['model'] = $this->model;
        $this->params['datatable'] = $this->datatablesBuilder
        ->addColumn([ 'data' => 'request_date', 'name' => 'request_date', 'title' => 'Date-No' ])
        ->addColumn([ 'data' => 'request_number', 'name' => 'request_number', 'title' => 'Purchase Request No' ])
        ->addColumn([ 'data' => 'pat_number', 'name' => 'pat_number', 'title' => 'PAT Number' ])
        ->addColumn([ 'data' => 'pic.name', 'name' => 'pic.name', 'title' => 'PIC' ])
        ->addColumn([ 'data' => 'total_item', 'name' => 'total_item', 'title' => 'Item' ])
        ->addColumn([ 'data' => 'total_price', 'name' => 'total_price', 'title' => 'Total Amount' ])
        ->addColumn([ 'data' => 'request_status', 'name' => 'request_status', 'title' => 'Progress Status' ])
        ->addColumn([ 'data' => 'action', 'name' => 'action', 'title' => 'Action' ])
        ->parameters([
          'initComplete' => 'function() {
            $.getScript("'. asset("js/utomodeck.js") .'");
            $.getScript("'. asset("js/purchase/request-index.js") .'");
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
        $keepPurchaseDetails = [];
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
            $purchaseDetails = $request->purchase_details;

            $params = $request->all();
            $params['request_date'] = date('Y-m-d', strtotime($request->request_date));
            $params['request_status'] = $this->model::REQUEST_PENDING;
            $params['order_status'] = $this->model::DEFAULT_ORDER_STATUS;

            unset($params['submit'], $params['_token'], $params['purchaseDetails']);

            $purchase = $this->model->where('id', $id)->first();
            $purchase->update($params);

            if(!empty($purchaseDetails) && count($purchaseDetails) > 0) {
                foreach ($purchaseDetails as $key => $purchaseDetail) {
                    $id = $purchaseDetail['id'];

                    $purchaseDetail['quantity'] = str_replace(',', '', $purchaseDetail['quantity']);
                    $purchaseDetail['estimation_price'] = str_replace(',', '', $purchaseDetail['estimation_price']);
                    $purchaseDetail['amount'] = str_replace(',', '', $purchaseDetail['amount']);

                    $currentPurchaseDetail = $purchase->purchase_details()->where('id', $id)->first();

                    if(!empty($currentPurchaseDetail)) {
                        $currentPurchaseDetail->update($purchaseDetail);
                        $keepPurchaseDetails[] = $currentPurchaseDetail->id;
                        continue;
                    }

                    $newPurchaseDetail = $purchase->purchase_details()->create($purchaseDetail);
                    $keepPurchaseDetails[] = $newPurchaseDetail->id;
                    $items = $this->items::where('name', $purchaseDetail['item_name'])->get();
                    $itemsCount = $items->count();
                    if($itemsCount < 1){
                        $insertItem = Item::create([
                            'name' => $purchaseDetail['item_name'],
                            'price' => $purchaseDetail['estimation_price'],
                            'item_category_id' =>  1
                        ]);
                    }
                }

                // hapus yang gk ada di request
                $purchase->purchase_details()->whereNotIn('id', $keepPurchaseDetails)->delete();
            }

            if($submitAction == 'save_print') {
                $redirectOnSuccess .= "?print=" .$purchase->id;
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
            $purchase = $this->model->find($id);
            $purchase->purchase_details()->delete();
            $purchase->delete();

            DB::commit();
            return response()->json([], 204);
        } catch (\Throwable $th) {
            DB::rollback();
            return response()->json(['message' => $th->getMessage()], 500);
        }
    }

    public function print(Request $request, $id)
    {
        try {
            
            DB::beginTransaction();
            $purchase = $this->model->where('id', $id)->with([
                'purchase_details', 'branch', 'vendor','pic', 'vendor.profile' , 'vendor.profile.default_address',
                'vendor.profile.default_address.region_city', 'vendor.profile.default_address.region_district'])->first();
            $params['model'] = $purchase;

            // if(!empty($purchase->request_log_print)) {
            //     //print with watermark
            //     return \PrintFile::copy($this->routeView . '.pdf', $params, 'Purchase-Request-' . $purchase->request_number);
            // }

            //print without watermark
            // LogPrint::create([
            //     'transaction_code' => \Config::get('transactions.purchase_request.code'),
            //     'transaction_number' => $purchase->request_number,
            //     'employee_id' => Auth()->user()->id,
            //     'date' => now()
            // ]);

            $purchase->purchase_details()->update(['request_status' => $this->model::REQUEST_ACCEPT]);
            $purchase->request_status = $this->model::REQUEST_ACCEPT;
            $purchase->save();

            DB::commit();
            return \PrintFile::original($this->routeView . '.pdf', $params, 'Purchase-Request-' . $purchase->request_number);
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
            'vendor_id' => ['required'],
            'branch_id' => ['required'],
            'request_by' => ['required'],
            'request_number' => ['required', 'unique:purchases,request_number' . $ignoredId],
            'request_date' => ['required'],
            'purchase_details.*.quantity' => ['required'],
            'purchase_details.*.estimation_price' => ['required'],
        ]);
    }
}
