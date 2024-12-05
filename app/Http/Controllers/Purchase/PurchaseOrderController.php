<?php

namespace App\Http\Controllers\Purchase;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;
use Yajra\DataTables\DataTables;
use Yajra\DataTables\Html\Builder;

use App\Models\LogPrint;
use App\Models\Purchase\Purchase;
use App\Models\Purchase\PurchaseReceipt;
use App\Models\Master\Material\RawMaterial;
use App\Models\Finance\COA;
use App\Models\Finance\FinanceJournal;
use Carbon\Carbon;

class PurchaseOrderController extends Controller
{
  private $route = 'purchase/order';
  private $routeView = 'purchase.order';
  private $params = [];

  public function __construct (Builder $datatablesBuilder)
  {
    $this->model = new Purchase();
    $this->datatablesBuilder = $datatablesBuilder;

    $this->params['route'] = $this->route;
    $this->params['routeView'] = $this->routeView;
    $this->coa = new COA();
    $this->journal = new FinanceJournal();
    $this->no_urut = $this->journal->latest()->pluck("no_transaksi")->first() + 1;
    $this->params['coa'] = $this->coa->where("nama_akun", "like", "%biaya%")->orWhere("nama_akun", "like", "%beban%")->whereNotNull("lk")->get();
    $this->params["sumber_biaya"] = $this->coa->where("nama_akun", "like", "%kas%")->orWhere("nama_akun", "like", "%bank%")->whereNotNull("lk")->get();
   
  }

  public function search(Request $request)
  {
    $where = "order_status = " . $this->model::ORDER_PROCESS;
    $where .= " and request_status = " . $this->model::REQUEST_ACCEPT;
    $response = [];

    if ($request->searchKey) {
      $where .= " and order_number like '%{$request->searchKey}%'";
    }

    try {
      $results = $this->model->whereRaw($where)
      ->get()
      ->makeHidden(['created_at', 'updated_at']);

      foreach ($results as $key => $result) {
        $result->name = $result->order_number;
      }

      $response['results'] = $results;
    } catch (\Exception $e) {
      return response(['message' => $e->getMessage()], 500);
    }

    return response()->json($response, 200);
  }

  /**
  * used in menu :
  * purchase order index to get detail
  * purchase receipt
  */
  public function searchById ($id)
  {
    $result = $this->model->where('id', $id)->with([
      'purchase_details',
      'order_log_print' => function ($query) {
        $query->with(['employee']);
      },
      ])->first();

      $result->name = $result->order_number;
      return response()->json($result, 200);
    }

    /**
    * Display a listing of the resource.
    *
    * @return \Illuminate\Http\Response
    */
    public function index(Request $request)
    {
      $orderStatus = [
        $this->model::ORDER_PENDING => ['label' => 'on request', 'label-color' => 'yellow'],
        $this->model::ORDER_PROCESS => ['label' => 'on process', 'label-color' => 'blue'],
        $this->model::ORDER_FINISH => ['label' => 'finish', 'label-color' => 'green'],
        $this->model::ORDER_CANCEL => ['label' => 'cancel', 'label-color' => 'red'],
      ];

      if ($request->ajax()) {
        $query = $this->model::with(['purchase_details', 'pic', 'vendor'])
        ->where('request_status', $this->model::REQUEST_ACCEPT)
        ->whereNotNull('order_number');

        return Datatables::of($query)
        ->addColumn('total_item', function (Purchase $purchase) {
          return $purchase->purchase_details->count();
        })
        ->editColumn('total_price', function (Purchase $purchase) {
          return \Rupiah::format($purchase->total_price);
        })
        ->editColumn('order_status', function (Purchase $purchase) use ($orderStatus) {
          return '<small class="label bg-'. $orderStatus[$purchase->order_status]['label-color'] . '">' . $orderStatus[$purchase->order_status]['label'] . '</small>';
        })
        ->editColumn('order_date', function (Purchase $purchase) {
          $roleUser = request()->user()->role->name;
          $isSuperAdmin = $roleUser === 'super_admin';

          return '<a class="has-ajax-form text-red" href=""
          data-toggle="modal"
          data-target="#ajax-form"
          data-form-url="' . url($this->route) . '"
          data-load="'. url($this->route . '/' . $purchase->id . '/ajax-form') . '"
          data-is-superadmin="'. $isSuperAdmin . '">
          ' . $purchase->order_date->format('m/d/Y') . ' - ' . $purchase->id . '
          </a>';
        })
        // ->editColumn('request_number', function (Purchase $purchase) {
        //   return '<a class="text-red"
        //   target="_blank"
        //   href="'. url('/purchase/request/' . $purchase->id . '/edit') . '">
        //   ' . $purchase->request_number . '
        //   </a>';
        // })
        ->addColumn('action', function (Purchase $purchase) {
          return \TransAction::table($this->route, $purchase, 'order_number', $purchase->order_log_print);
        })
        ->rawColumns(['order_date', 'request_number', 'order_status', 'action'])
        ->make(true);
      }

      $this->params['model'] = $this->model;
      $this->params['datatable'] = $this->datatablesBuilder
      ->addColumn([ 'data' => 'order_date', 'name' => 'order_date', 'title' => 'Date-No' ])
      ->addColumn([ 'data' => 'order_number', 'name' => 'order_number', 'title' => 'Purchase Order No' ])
      // ->addColumn([ 'data' => 'request_number', 'name' => 'request_number', 'title' => 'Purchase Request No' ])
      ->addColumn([ 'data' => 'pic.name', 'name' => 'pic.name', 'title' => 'PIC' ])
      ->addColumn([ 'data' => 'total_item', 'name' => 'total_item', 'title' => 'Item' ])
      ->addColumn([ 'data' => 'total_price', 'name' => 'total_price', 'title' => 'Total Amount' ])
      ->addColumn([ 'data' => 'order_status', 'name' => 'order_status', 'title' => 'Progress Status' ])
      ->addColumn([ 'data' => 'action', 'name' => 'action', 'title' => 'Action' ])
      ->parameters([
        'initComplete' => 'function() {
          $.getScript("'. asset("js/utomodeck.js") .'");
          $.getScript("'. asset("js/purchase/order-index.js") .'");
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
      $this->params['model']['order_number'] = \RunningNumber::generate('purchases', 'order_number', \Config::get('transactions.purchase_order.code'));
      return view($this->routeView . '.create', $this->params);
    }

    /**
    * cari id purchase, update untuk rubah status order, scenario saat ini,
    * tidak bisa insert kalau tidak input request number
    * @param  \Illuminate\Http\Request  $request
    * @return \Illuminate\Http\Response
    */
    public function store(Request $request)
    {
      // return dd($request->all());

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

      // check request->id ada atau tidak, kalau tidak, kembalikan

      try {
        DB::beginTransaction();
        $submitAction = $request->submit;
        $purchaseDetails = $request->purchase_details;

        $params = $request->all();
        // dd($params);

        // $pr = [
        //   "request_date" => date('Y-m-d', strtotime($request->order_date)),
        //   "request_type" => 0,
        //   "request_number" => \RunningNumber::generate('purchases', 'request_number', \Config::get('transactions.purchase_request.code')),
        //   "request_by" => $request->request_by,
        //   "request_status" => 1,
        //   "vendor_id" => $request->vendor_id,
        //   "warehouse_id" => $request->warehouse_id,
        //   "total_price" => $request->total_price
        // ];

        // $item_create = $this->model::create($pr);

        $params['order_date'] = date('Y-m-d', strtotime($request->order_date));
        $params['request_status'] = $this->model::REQUEST_ACCEPT;

        unset(
          $params['request_number'],
          $params['submit'],
          $params['_token'],
          $params['purchaseDetails']
        );

        $item = $this->model::where('id', $params['id'])->first();
        $item->update($params);

        // if(!empty($purchaseDetails) && count($purchaseDetails) > 0) {
        //   foreach ($purchaseDetails as $key => $purchaseDetail) {
        //     $id = $purchaseDetail['id'];

        //     $purchaseDetail['order_status'] = $this->model::ORDER_PENDING;
        //     $purchaseDetail['quantity'] = str_replace(',', '', $purchaseDetail['quantity']);
        //     $purchaseDetail['estimation_price'] = str_replace(',', '', $purchaseDetail['estimation_price']);
        //     $purchaseDetail['amount'] = str_replace(',', '', $purchaseDetail['amount']);

        //     $currentPurchaseDetail = $item->purchase_details()->where('id', $id)->first();

        //     if(!empty($currentPurchaseDetail)) {
        //       $currentPurchaseDetail->update($purchaseDetail);
        //       $keepPurchaseDetails[] = $currentPurchaseDetail->id;
        //       continue;
        //     }

        //     $newPurchaseDetail = $item->purchase_details()->create($purchaseDetail);
        //     $keepPurchaseDetails[] = $newPurchaseDetail->id;
        //   }

        //   // update status request jadi cancel untuk item yang gk ada di request
        //   $item->purchase_details()->whereNotIn('id', $keepPurchaseDetails)->update([
        //     'request_status' => $this->model::REQUEST_REJECT
        //   ]);
        // }

        // if ($request->filled("sumber_biaya")) {
        //   $journal = [];

        //   $total_biaya = 0;
        //   foreach($request->akun_biaya as $key => $value) {
        //     $arr = [
        //       "no_transaksi" => $this->no_urut,
        //       "kode_akun" => $request->akun_biaya[$key], // Beban
        //       "pos" => 1,
        //       "nominal" => $request->jml_biaya[$key],
        //       "model" => "purchase-order",
        //       "ref" => $request->order_number,
        //       "created_at" => $this->journal->freshTimestamp(),
        //       "updated_at" => $this->journal->freshTimestamp(),
        //     ];
        //     array_push($journal, $arr);
        //     $total_biaya += $request->jml_biaya[$key];
        //   }

          // $gl_kredit = [
          //   "no_transaksi" => $this->no_urut,
          //   "kode_akun" => $request->sumber_biaya, // Sumber Biaya
          //   "pos" => 2,
          //   "nominal" => $total_biaya,
          //   "model" => "purchase-order",
          //   "ref" => $request->order_number,
          //   "created_at" => $this->journal->freshTimestamp(),
          //   "updated_at" => $this->journal->freshTimestamp(),
          // ];
          // array_push($journal, $gl_kredit);

          // $this->journal->insert($journal);
        // }

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
    public function show(Request $request, $id)
    {
      //
      $orderStatus = [
        $this->model::ORDER_PENDING => ['label' => 'on request', 'label-color' => 'yellow'],
        $this->model::ORDER_PROCESS => ['label' => 'on process', 'label-color' => 'blue'],
        $this->model::ORDER_FINISH => ['label' => 'finish', 'label-color' => 'green'],
        $this->model::ORDER_CANCEL => ['label' => 'cancel', 'label-color' => 'red'],
      ];

      if ($request->ajax()) {
        $query = $this->model::with(['purchase_details', 'pic', 'vendor'])
        ->where('request_status', $this->model::REQUEST_ACCEPT)
        ->whereNotNull('order_number');
        if ($request->filled("periode_awal") && $request->filled("periode_akhir")) {
          $query = $query->whereBetween("request_date", [Carbon::parse($request->periode_awal)->startOfDay(), Carbon::parse($request->periode_akhir)->endOfDay()]);
        }
        if($request->filled("filter_po_no")){
          $query = $query->where("order_number", "like", "%$request->filter_po_no%");
        }
        if($request->filled("filter_pr_no")){
          $query = $query->where("request_number", "like", "%$request->filter_pr_no%");
        }
        if($request->filled("filter_customer")){
          $query = $query->whereHas("pic", function($q) use($request){
            $q->where("name", "like", "%$request->filter_customer%");
          });
        }
        if($request->filled("filter_status")){
          $query = $query->where("order_status", $request->filter_status);
        }

        // return dd($query->get());

        return Datatables::of($query)
        ->addColumn('total_item', function (Purchase $purchase) {
          return $purchase->purchase_details->count();
        })
        ->editColumn('total_price', function (Purchase $purchase) {
          return \Rupiah::format($purchase->total_price);
        })
        ->editColumn('order_status', function (Purchase $purchase) use ($orderStatus) {
          return '<small class="label bg-'. $orderStatus[$purchase->order_status]['label-color'] . '">' . $orderStatus[$purchase->order_status]['label'] . '</small>';
        })
        ->editColumn('order_date', function (Purchase $purchase) {
          $roleUser = request()->user()->role->name;
          $isSuperAdmin = $roleUser === 'super_admin';

          return '<a class="has-ajax-form text-red" href=""
          data-toggle="modal"
          data-target="#ajax-form"
          data-form-url="' . url($this->route) . '"
          data-load="'. url($this->route . '/' . $purchase->id . '/ajax-form') . '"
          data-is-superadmin="'. $isSuperAdmin . '">
          ' . $purchase->order_date->format('m/d/Y') . ' - ' . $purchase->id . '
          </a>';
        })
        ->editColumn('request_number', function (Purchase $purchase) {
          return '<a class="text-red"
          target="_blank"
          href="'. url('/purchase/request/' . $purchase->id . '/edit') . '">
          ' . $purchase->request_number . '
          </a>';
        })
        ->addColumn('action', function (Purchase $purchase) {
          return \TransAction::table($this->route, $purchase, 'purchase_receives.number', $purchase->order_log_print);
        })
        ->rawColumns(['order_date', 'request_number', 'order_status', 'action'])
        ->make(true);
      }

      $this->params['model'] = $this->model;
      $this->params['datatable'] = $this->datatablesBuilder
      ->addColumn([ 'data' => 'order_date', 'name' => 'order_date', 'title' => 'Date-No' ])
      ->addColumn([ 'data' => 'order_number', 'name' => 'order_number', 'title' => 'Purchase Order No' ])
      ->addColumn([ 'data' => 'request_number', 'name' => 'request_number', 'title' => 'Purchase Request No' ])
      ->addColumn([ 'data' => 'pic.name', 'name' => 'pic.name', 'title' => 'PIC' ])
      ->addColumn([ 'data' => 'total_item', 'name' => 'total_item', 'title' => 'Item' ])
      ->addColumn([ 'data' => 'total_price', 'name' => 'total_price', 'title' => 'Total Amount' ])
      ->addColumn([ 'data' => 'order_status', 'name' => 'order_status', 'title' => 'Progress Status' ])
      ->addColumn([ 'data' => 'action', 'name' => 'action', 'title' => 'Action' ])
      ->parameters([
        'initComplete' => 'function() {
          $.getScript("'. asset("js/utomodeck.js") .'");
          $.getScript("'. asset("js/purchase/order-index.js") .'");
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
        $params['order_date'] = date('Y-m-d', strtotime($request->order_date));
        $params['order_status'] = $this->model::ORDER_PENDING;

        unset(
          $params['id'],
          $params['request_number'],
          $params['submit'],
          $params['_token'],
          $params['purchaseDetails']
        );

        $purchase = $this->model->where('id', $id)->first();
        $purchase->update($params);

        // if(!empty($purchaseDetails) && count($purchaseDetails) > 0) {
        //   foreach ($purchaseDetails as $key => $purchaseDetail) {
        //     $id = $purchaseDetail['id'];

        //     $purchaseDetail['order_status'] = $this->model::ORDER_PENDING;
        //     $purchaseDetail['quantity'] = str_replace(',', '', $purchaseDetail['quantity']);
        //     $purchaseDetail['estimation_price'] = str_replace(',', '', $purchaseDetail['estimation_price']);
        //     $purchaseDetail['amount'] = str_replace(',', '', $purchaseDetail['amount']);

        //     $currentPurchaseDetail = $purchase->purchase_details()->where('id', $id)->first();

        //     if(!empty($currentPurchaseDetail)) {
        //       $currentPurchaseDetail->update($purchaseDetail);
        //       $keepPurchaseDetails[] = $currentPurchaseDetail->id;
        //       continue;
        //     }

        //     $newPurchaseDetail = $purchase->purchase_details()->create($purchaseDetail);
        //     $keepPurchaseDetails[] = $newPurchaseDetail->id;
        //   }

        //   /**
        //   * untuk item yang gk ada di request,
        //   * update status order jadi cancel (kalau submit= save_print, karena berikutnya
        //   * tidak mungkin di update lagi)
        //   * update status order jadi pending (kalau submit= save, karena ada kemungkinan di update lagi)
        //   *   */
        //   $purchase->purchase_details()->whereNotIn('id', $keepPurchaseDetails)->update([
        //     'order_status' => ($submitAction == 'save_print') ? $this->model::ORDER_CANCEL : $this->model::ORDER_PENDING
        //   ]);
        // }

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
          'message' => str_replace(".", " ", $this->routeView) . ' : ' . $th,
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
        $purchase = $this->model->where('id', $id)->first();
        $purchase->purchase_details()->update([
          'order_status' => $this->model::ORDER_PENDING
        ]);
        LogPrint::where("transaction_code", "PO")->where("transaction_number", $purchase->order_number)->delete();
        $purchase->update([
          'order_date' => null,
          'order_number' => null,
          'order_status' => $this->model::ORDER_PENDING
        ]);

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
          'purchase_details', 'vendor','pic', 'vendor.profile' , 'vendor.profile.default_address',
          'vendor.profile.default_address.region_city', 'vendor.profile.default_address.region_district'])->first();
        $params['model'] = $purchase;

        /*if(!empty($purchase->order_log_print)) {
          //print with watermark
          return \PrintFile::copy($this->routeView . '.pdf', $params, 'Purchase-Order-' . $purchase->order_number);
        }*/

        //print without watermark
        LogPrint::create([
          'transaction_code' => \Config::get('transactions.purchase_order.code'),
          'transaction_number' => $purchase->order_number,
          'employee_id' => Auth()->user()->id,
          'date' => now()
        ]);

        $purchase->purchase_details()
        ->where(['order_status' => $this->model::ORDER_PENDING])
        ->update(['order_status' => $this->model::ORDER_PROCESS]);
        $purchase->order_status = $this->model::ORDER_PROCESS;
        $purchase->save();

        DB::commit();
        // dd($params);
        // return view($this->routeView . '.pdf', $params);

        return \PrintFile::original($this->routeView . '.pdf', $params, 'Purchase-Order-' . $purchase->order_number);
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
        // 'request_number' => ['required'],
        'vendor_id' => ['required'],
        'warehouse_id' => ['required'],
        'request_by' => ['required'],
        'order_number' => ['required', 'unique:purchases,order_number' . $ignoredId],
        'order_date' => ['required'],
        'purchase_details.*.quantity' => ['required'],
        'purchase_details.*.estimation_price' => ['required'],
      ]);
    }
  }
