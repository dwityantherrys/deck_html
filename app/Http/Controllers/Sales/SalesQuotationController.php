<?php

namespace App\Http\Controllers\Sales;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;
use Yajra\DataTables\DataTables;
use Yajra\DataTables\Html\Builder;

use App\Models\LogPrint;
use App\Models\Sales\Sales;
use App\Models\Master\Item\ItemMaterial;
use App\Models\Master\Material\RawMaterial;
use App\Models\Master\Payment\PaymentBankChannel;
use Carbon\Carbon;
use Maatwebsite\Excel\Facades\Excel;
use App\Exports\SalesQuotationExport;

class SalesQuotationController extends Controller
{
  private $route = 'sales/quotation';
  private $routeView = 'sales.quotation';
  private $params = [];

  public function __construct (Builder $datatablesBuilder)
  {
    $this->model = new Sales();
    $this->datatablesBuilder = $datatablesBuilder;

    $this->params['route'] = $this->route;
    $this->params['routeView'] = $this->routeView;
    $this->params['paymentBankChannels'] = PaymentBankChannel::active()->get();
    $this->params['transaction_channels'] = [
      Sales::TRANSACTION_CHANNEL_WEB => ['label' => 'website', 'label-color' => 'green', 'icon' => 'fa fa-desktop'],
      Sales::TRANSACTION_CHANNEL_MOBILE => ['label' => 'mobile', 'label-color' => 'blue', 'icon' => 'fa fa-mobile']
    ];
  }

  public function search(Request $request)
  {
    $where = "order_status = " . $this->model::ORDER_PENDING;
    $where .= " and quotation_status = " . $this->model::QUOTATION_ACCEPT;
    $where .= " and order_date is null";
    $response = [];

    if ($request->searchKey) {
      $where .= " and quotation_number like '%{$request->searchKey}%'";
    }

    try {
      $results = $this->model->whereRaw($where)
      ->get()
      ->makeHidden(['created_at', 'updated_at']);

      foreach ($results as $key => $result) {
        $result->name = $result->quotation_number;
      }

      $response['results'] = $results;
    } catch (\Exception $e) {
      return response(['message' => $e->getMessage()], 500);
    }

    return response()->json($response, 200);
  }

  public function searchById ($id)
  {
    $result = $this->model->where('id', $id)->with(['sales_details' => function ($query) {
      $query->withTrashed();
    },
    'quotation_log_print' => function ($query) {
      $query->with(['employee']);
    }])->withTrashed()->first();

    foreach ($result->sales_details as $salesDetail) {
      $salesDetail->length_options = [];
      $salesDetail->length_selected = $salesDetail->is_custom_length ? 0 : $salesDetail->length;
    }

    $result->name = $result->quotation_number;
    return response()->json($result, 200);
  }

  /**
  * Display a listing of the resource.
  *
  * @return \Illuminate\Http\Response
  */
  public function index(Request $request)
  {
    $quotationStatus = [
      $this->model::QUOTATION_PENDING => ['label' => 'on request', 'label-color' => 'yellow'],
      $this->model::QUOTATION_ACCEPT => ['label' => 'accept', 'label-color' => 'blue'],
      $this->model::QUOTATION_REJECT => ['label' => 'cancel', 'label-color' => 'red']
    ];

    $transactionChannels = $this->params['transaction_channels'];

    if ($request->ajax()) {
      $query = $this->model::with(['sales_details', 'pic', 'customer'])->withTrashed();
      return Datatables::of($query)->setRowAttr([
        'style' => function(Sales $sales) {
          if(empty($sales->deleted_at)) return;
          return 'background: #ffb9b9';
        }
      ])
      ->addColumn('total_item', function (Sales $sales) {
        return $sales->sales_details->count();
      })
      ->editColumn('grand_total_price', function (Sales $sales) {
        return \Rupiah::format($sales->grand_total_price);
      })
      ->editColumn('quotation_status', function (Sales $sales) use ($quotationStatus) {
        return '<small class="label bg-'. $quotationStatus[$sales->quotation_status]['label-color'] . '">' . $quotationStatus[$sales->quotation_status]['label'] . '</small>';
      })
      ->editColumn('transaction_channel', function (Sales $sales) use ($transactionChannels) {
        return '<small class="label bg-'. $transactionChannels[$sales->transaction_channel]['label-color'] . '">'
        . '<i class="'. $transactionChannels[$sales->transaction_channel]['icon'] . '" style="margin-right: 5px;"></i>'
        . $transactionChannels[$sales->transaction_channel]['label'] .
        '</small>';
      })
      ->editColumn('quotation_date', function (Sales $sales) {
        $roleUser = request()->user()->role->name;
        $isSuperAdmin = $roleUser === 'super_admin';

        return '<a class="has-ajax-form text-red" href=""
        data-toggle="modal"
        data-target="#ajax-form"
        data-form-url="' . url($this->route) . '"
        data-load="'. url($this->route . '/' . $sales->id . '/ajax-form') . '"
        data-is-superadmin="'. $isSuperAdmin . '">
        ' . $sales->quotation_date->format('m/d/Y') . ' - ' . $sales->id . '
        </a>';
      })
      ->addColumn('action', function (Sales $sales) {
        return \TransAction::table($this->route, $sales, 'order_number', $sales->quotation_log_print);
      })
      ->rawColumns(['quotation_date', 'quotation_status', 'transaction_channel', 'action'])
      ->make(true);
    }

    $this->params['model'] = $this->model;
    $this->params['datatable'] = $this->datatablesBuilder
    ->addColumn([ 'data' => 'quotation_date', 'name' => 'quotation_date', 'title' => 'Date-No' ])
    ->addColumn([ 'data' => 'quotation_number', 'name' => 'quotation_number', 'title' => 'Sales Quotation No' ])
    ->addColumn([ 'data' => 'pic.name', 'name' => 'pic.name', 'title' => 'PIC' ])
    ->addColumn([ 'data' => 'total_item', 'name' => 'total_item', 'title' => 'Item' ])
    ->addColumn([ 'data' => 'grand_total_price', 'name' => 'grand_total_price', 'title' => 'Grand Total' ])
    ->addColumn([ 'data' => 'quotation_status', 'name' => 'quotation_status', 'title' => 'Progress Status' ])
    ->addColumn([ 'data' => 'transaction_channel', 'name' => 'transaction_channel', 'title' => 'Channel' ])
    ->addColumn([ 'data' => 'action', 'name' => 'action', 'title' => 'Action' ])
    ->parameters([
      'initComplete' => 'function() {
        $.getScript("'. asset("js/utomodeck.js") .'");
        $.getScript("'. asset("js/sales/quotation-index.js") .'");
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
    $this->params['model']['quotation_number'] = \RunningNumber::generate('sales', 'quotation_number', \Config::get('transactions.sales_quotation.code'));

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
      $defaultTax = 10;
      $submitAction = $request->submit;
      $salesDetails = $request->sales_details;

      $params = $request->all();
      $params['quotation_date'] = date('Y-m-d', strtotime($request->quotation_date));
      $params['quotation_status'] = $this->model::QUOTATION_PENDING;
      $params['order_status'] = $this->model::DEFAULT_ORDER_STATUS;
      $params['transaction_channel'] = $this->model::TRANSACTION_CHANNEL_WEB;
      $params['discount'] = str_replace(',', '', $request->discount);
      $params['downpayment'] = str_replace(',', '', $request->downpayment);
      $params['tax'] = $defaultTax;

      unset($params['submit'], $params['_token'], $params['salesDetails']);

      // dd($params);
      $sales = $this->model::create($params);

      if(!empty($salesDetails) && count($salesDetails) > 0) {
        foreach ($salesDetails as $key => $salesDetail) {
          $sdTotalPrice = str_replace(',', '', $salesDetail['total_price']);
          $itemMaterial = ItemMaterial::find($salesDetail['item_material_id']);
          $item = $itemMaterial->item;

          unset($salesDetail['length_selected']);

          $salesDetail['quotation_status'] = $this->model::QUOTATION_PENDING;
          $salesDetail['order_status'] = $this->model::DEFAULT_ORDER_STATUS;
          $salesDetail['is_custom_length'] = $salesDetail['is_custom_length'] == 'true' ? 1 : 0;
          $salesDetail['width'] = $item->width;
          $salesDetail['height'] = $item->height;
          $salesDetail['weight'] = $item->weight*$salesDetail['length'];
          $salesDetail['quantity'] = str_replace(',', '', $salesDetail['quantity']);
          $salesDetail['price'] = str_replace(',', '', $salesDetail['price']);
          $salesDetail['total_price'] = $sdTotalPrice;

          $sales->sales_details()->create($salesDetail);
        }
      }

      if($submitAction == 'save_print') {
        $redirectOnSuccess .= "?print=" .$sales->id;
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
    $quotationStatus = [
      $this->model::QUOTATION_PENDING => ['label' => 'on request', 'label-color' => 'yellow'],
      $this->model::QUOTATION_ACCEPT => ['label' => 'accept', 'label-color' => 'blue'],
      $this->model::QUOTATION_REJECT => ['label' => 'cancel', 'label-color' => 'red']
    ];

    $transactionChannels = $this->params['transaction_channels'];

    if ($request->ajax()) {
      $query = $this->model::with(['sales_details', 'pic', 'customer'])->withTrashed();
      if ($request->filled("periode_awal") && $request->filled("periode_akhir")) {
        $query = $query->whereBetween("quotation_date", [Carbon::parse($request->periode_awal)->startOfDay(), Carbon::parse($request->periode_akhir)->endOfDay()]);
      }
      if ($request->filled("filter_sq_no")) {
        $query = $query->where("quotation_number", "like", "%$request->filter_sq_no%");
      }
      if ($request->filled("filter_pic")) {
        $query = $query->whereHas("pic", function($q) use($request) {
          $q->where("name", "like", "%$request->filter_pic%");
        });
      }
      if ($request->filled("filter_status")) {
        $query = $query->where("quotation_status", "$request->filter_status");
      }
      return Datatables::of($query)->setRowAttr([
        'style' => function(Sales $sales) {
          if(empty($sales->deleted_at)) return;
          return 'background: #ffb9b9';
        }
      ])
      ->addColumn('total_item', function (Sales $sales) {
        return $sales->sales_details->count();
      })
      ->editColumn('grand_total_price', function (Sales $sales) {
        return \Rupiah::format($sales->grand_total_price);
      })
      ->editColumn('quotation_status', function (Sales $sales) use ($quotationStatus) {
        return '<small class="label bg-'. $quotationStatus[$sales->quotation_status]['label-color'] . '">' . $quotationStatus[$sales->quotation_status]['label'] . '</small>';
      })
      ->editColumn('transaction_channel', function (Sales $sales) use ($transactionChannels) {
        return '<small class="label bg-'. $transactionChannels[$sales->transaction_channel]['label-color'] . '">'
        . '<i class="'. $transactionChannels[$sales->transaction_channel]['icon'] . '" style="margin-right: 5px;"></i>'
        . $transactionChannels[$sales->transaction_channel]['label'] .
        '</small>';
      })
      ->editColumn('quotation_date', function (Sales $sales) {
        $roleUser = request()->user()->role->name;
        $isSuperAdmin = $roleUser === 'super_admin';

        return '<a class="has-ajax-form text-red" href=""
        data-toggle="modal"
        data-target="#ajax-form"
        data-form-url="' . url($this->route) . '"
        data-load="'. url($this->route . '/' . $sales->id . '/ajax-form') . '"
        data-is-superadmin="'. $isSuperAdmin . '">
        ' . $sales->quotation_date->format('m/d/Y') . ' - ' . $sales->id . '
        </a>';
      })
      ->addColumn('action', function (Sales $sales) {
        return \TransAction::table($this->route, $sales, 'order_number', $sales->quotation_log_print);
      })
      ->rawColumns(['quotation_date', 'quotation_status', 'transaction_channel', 'action'])
      ->make(true);
    }

    $this->params['model'] = $this->model;
    $this->params['datatable'] = $this->datatablesBuilder
    ->addColumn([ 'data' => 'quotation_date', 'name' => 'quotation_date', 'title' => 'Date-No' ])
    ->addColumn([ 'data' => 'quotation_number', 'name' => 'quotation_number', 'title' => 'Sales Quotation No' ])
    ->addColumn([ 'data' => 'pic.name', 'name' => 'pic.name', 'title' => 'PIC' ])
    ->addColumn([ 'data' => 'total_item', 'name' => 'total_item', 'title' => 'Item' ])
    ->addColumn([ 'data' => 'grand_total_price', 'name' => 'grand_total_price', 'title' => 'Grand Total' ])
    ->addColumn([ 'data' => 'quotation_status', 'name' => 'quotation_status', 'title' => 'Progress Status' ])
    ->addColumn([ 'data' => 'transaction_channel', 'name' => 'transaction_channel', 'title' => 'Channel' ])
    ->addColumn([ 'data' => 'action', 'name' => 'action', 'title' => 'Action' ])
    ->parameters([
      'initComplete' => 'function() {
        $.getScript("'. asset("js/utomodeck.js") .'");
        $.getScript("'. asset("js/sales/quotation-index.js") .'");
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
    $result = $this->model->where('id', $id)->with([
      'sales_details' => function ($query) {
        $query->withTrashed();
      },
      'quotation_log_print' => function ($query) {
        $query->with(['employee']);
      }
      ])->withTrashed()->first();

      foreach ($result->sales_details as $salesDetail) {
        $salesDetail->length_options = [];
        $salesDetail->length_selected = $salesDetail->is_custom_length ? 0 : $salesDetail->length;
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
      $keepSalesDetails = [];
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
        $salesDetails = $request->sales_details;

        $params = $request->all();
        $params['quotation_date'] = date('Y-m-d', strtotime($request->quotation_date));
        $params['quotation_status'] = $this->model::QUOTATION_PENDING;
        $params['order_status'] = $this->model::DEFAULT_ORDER_STATUS;
        $params['transaction_channel'] = $this->model::TRANSACTION_CHANNEL_WEB;
        $params['discount'] = str_replace(',', '', $request->discount);
        $params['downpayment'] = str_replace(',', '', $request->downpayment);

        unset($params['submit'], $params['_token'], $params['salesDetails']);

        $sales = $this->model->where('id', $id)->first();
        $sales->update($params);

        if(!empty($salesDetails) && count($salesDetails) > 0) {
          foreach ($salesDetails as $key => $salesDetail) {
            $id = $salesDetail['id'];
            $itemMaterial = ItemMaterial::find($salesDetail['item_material_id']);
            $item = $itemMaterial->item;

            unset($params['length_selected']);

            $salesDetail['quotation_status'] = $this->model::QUOTATION_PENDING;
            $salesDetail['order_status'] = $this->model::DEFAULT_ORDER_STATUS;
            $salesDetail['is_custom_length'] = $salesDetail['is_custom_length'] == 'true' ? 1 : 0;
            $salesDetail['width'] = $item->width;
            $salesDetail['height'] = $item->height;
            $salesDetail['weight'] = $item->weight*$salesDetail['length'];
            $salesDetail['quantity'] = str_replace(',', '', $salesDetail['quantity']);
            $salesDetail['price'] = str_replace(',', '', $salesDetail['price']);
            $salesDetail['total_price'] = str_replace(',', '', $salesDetail['total_price']);
            // belum hitung kalau custom length

            $currentSalesDetail = $sales->sales_details()->where('id', $id)->first();

            if(!empty($currentSalesDetail)) {
              $currentSalesDetail->update($salesDetail);
              $keepSalesDetails[] = $currentSalesDetail->id;
              continue;
            }

            $newSalesDetail = $sales->sales_details()->create($salesDetail);
            $keepSalesDetails[] = $newSalesDetail->id;
          }

          // hapus yang gk ada di request
          $sales->sales_details()->whereNotIn('id', $keepSalesDetails)->delete();
        }

        if($submitAction == 'save_print') {
          $redirectOnSuccess .= "?print=" .$sales->id;
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
    public function destroy(Request $request, $id)
    {
      try {
        DB::beginTransaction();

        $sales = $this->model->find($id);
        $sales->quotation_status = $this->model::QUOTATION_REJECT;
        $sales->canceled_reason = $request->canceled_reason;
        $sales->save();

        $sales->sales_details()->update(['quotation_status' => $this->model::QUOTATION_REJECT]);

        $sales->quotation_log_print()->delete();
        $sales->sales_details()->delete();
        $sales->delete();

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
        $sales = $this->model->find($id);
        $params['model'] = $sales;

        if(!empty($sales->quotation_log_print)) {
          if($isSuperAdmin) return \PrintFile::original($this->routeView . '.pdf', $params, 'Sales-Quotation-' . $sales->quotation_number);
          //print with watermark
          return \PrintFile::copy($this->routeView . '.pdf', $params, 'Sales-Quotation-' . $sales->quotation_number);
        }

        //print without watermark
        LogPrint::create([
          'transaction_code' => \Config::get('transactions.sales_quotation.code'),
          'transaction_number' => $sales->quotation_number,
          'employee_id' => Auth()->user()->id,
          'date' => now()
        ]);

        $sales->sales_details()->update(['quotation_status' => $this->model::QUOTATION_ACCEPT]);
        $sales->quotation_status = $this->model::QUOTATION_ACCEPT;
        $sales->save();

        DB::commit();
        return \PrintFile::original($this->routeView . '.pdf', $params, 'Sales-Quotation-' . $sales->quotation_number);
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

    private function _validate ($request)
    {
      $ignoredId = !empty($request['id']) ? ','.$request['id'] : '';

      return Validator::make($request, [
        // 'sales_id' => ['required'],
        'customer_id' => ['required'],
        'warehouse_id' => ['required'],
        'created_by' => ['required'],
        'quotation_number' => ['required', 'unique:sales,quotation_number' . $ignoredId],
        'quotation_date' => ['required'],
        'sales_details.*.is_custom_length' => ['required'],
        'sales_details.*.length' => ['required'],
        'sales_details.*.sheet' => ['required'],
        'sales_details.*.quantity' => ['required'],
        'sales_details.*.price' => ['required'],
        'sales_details.*.total_price' => ['required'],
      ]);
    }

    public function export(Request $request)
    {
      $model = $this->model::with(['sales_details', 'pic', 'customer'])->withTrashed();
      if ($request->filled("filter_date")) {
        $model = $model->whereDate("quotation_date", Carbon::parse($request->filter_date));
      }
      if ($request->filled("filter_sq_no")) {
        $model = $model->where("quotation_number", "like", "%$request->filter_sq_no%");
      }
      if ($request->filled("filter_pic")) {
        $model = $model->whereHas("pic", function($q) use($request) {
          $q->where("name", "like", "%$request->filter_pic%");
        });
      }
      if ($request->filled("filter_status")) {
        $model = $model->where("quotation_status", "$request->filter_status");
      }

      $heading = $this->model->getTableColumns();
      return Excel::download(new SalesQuotationExport($model, $heading), 'salesquotation.xlsx');
    }
  }
