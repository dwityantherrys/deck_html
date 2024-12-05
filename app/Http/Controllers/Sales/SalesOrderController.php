<?php
namespace App\Http\Controllers\Sales;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;
use Yajra\DataTables\DataTables;
use Yajra\DataTables\Html\Builder;
use Illuminate\Database\Eloquent\Builder as qBuilder;

use App\Models\LogPrint;
use App\Models\Production\JobOrderDetail;
use App\Models\Sales\Sales;
use App\Models\Shipping\ShippingInstruction;
use App\Models\Master\Item\ItemMaterial;
use App\Models\Master\Item\Item;
use App\Models\Master\Material\RawMaterial;
use App\Models\Master\Payment\PaymentMethod;
use App\Models\Finance\AccountReceivable;
use App\Models\Master\Payment\PaymentBankChannel;
use App\Services\DataTableBase;
use Carbon\Carbon;
use Maatwebsite\Excel\Facades\Excel;
use App\Exports\SalesOrderExport;

class SalesOrderController extends Controller
{
  private $route = 'sales/order';
  private $routeView = 'sales.order';
  private $params = [];

  public function __construct (Builder $datatablesBuilder)
  {
    $this->model = new Sales();
    $this->datatablesBuilder = $datatablesBuilder;

    $this->params['route'] = $this->route;
    $this->params['routeView'] = $this->routeView;
    $this->params['paymentMethods'] = PaymentMethod::active()->where('available_at', 0)->select('id', 'name', 'image')->get();
    $this->params['paymentBankChannels'] = PaymentBankChannel::active()->get();
    $this->params['shippingMethods'] = [
      ShippingInstruction::METHOD_IS_PICKUP => ['label' => 'pickup', 'label-color' => 'green'],
      ShippingInstruction::METHOD_IS_PICKUP_POINT => ['label' => 'pickup pada pickup point', 'label-color' => 'yellow'],
      ShippingInstruction::METHOD_IS_DELIVERY => ['label' => 'kirim', 'label-color' => 'red'],
    ];
    $this->params['transaction_channels'] = [
      Sales::TRANSACTION_CHANNEL_WEB => ['label' => 'website', 'label-color' => 'green', 'icon' => 'fa fa-desktop'],
      Sales::TRANSACTION_CHANNEL_MOBILE => ['label' => 'mobile', 'label-color' => 'blue', 'icon' => 'fa fa-mobile']
    ];
  }

  /* dipakai di menu shipping instruction */
  public function search(Request $request)
  {
    $where = "order_status = " . $this->model::ORDER_PROCESS;
    $where .= " and quotation_status = " . $this->model::QUOTATION_ACCEPT;
    $response = [];

    if ($request->searchKey) {
      $where .= " and order_number like '%{$request->searchKey}%'";
    }

    try {
      $results = $this->model
      ->whereHas('sales_details', function (qBuilder $query) {
        $query->whereRaw('(sales_details.quantity - (
          select ifnull(sum(job_order_details.quantity), 0)
          from job_order_details
          where sales_detail_id = sales_details.id
          and deleted_at is null
          )) > 0');
        })
        ->whereRaw($where)
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
    * Sales order index to get detail
    * Sales receipt
    */
    public function searchById ($id)
    {
      $result = $this->model->where('id', $id)->with([
        'sales_details',
        'order_log_print' => function ($query) {
          $query->with(['employee']);
        }
        ])->first();

        foreach ($result->sales_details as $salesDetail) {
          $salesDetail->length_options = [];
          $salesDetail->length_selected = $salesDetail->is_custom_length ? 0 : $salesDetail->length;
        }

        $result->name = $result->order_number;
        return response()->json($result, 200);
      }

      public function searchShippingFormatById ($id)
      {
        $result = $this->model->where('id', $id)->with([ 'sales_details' ])->first();

        foreach ($result->sales_details as $salesDetail) {
          $itemMaterial = $salesDetail->item_material;

          $salesDetail->sales_detail_id = $salesDetail->id;
          $salesDetail->item_name = $itemMaterial->item->name . ' ' . $itemMaterial->material->name . ' ' . $itemMaterial->thick . 'mm ' .$itemMaterial->color->name;
          $salesDetail->length_formated = $salesDetail->length . ' m';
        }

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

        $transactionChannels = $this->params['transaction_channels'];

        if ($request->ajax()) {
          $query = $this->model::with(['sales_details', 'pic', 'customer'])
          ->whereNotNull('order_number');

          return Datatables::of($query)
          ->addColumn('total_item', function (Sales $sales) {
            return $sales->sales_details->count();
          })
          ->editColumn('grand_total_price', function (Sales $sales) {
            return \Rupiah::format($sales->grand_total_price);
          })
          ->editColumn('order_status', function (Sales $sales) use ($orderStatus) {
            return '<small class="label bg-'. $orderStatus[$sales->order_status]['label-color'] . '">' . $orderStatus[$sales->order_status]['label'] . '</small>';
          })
          ->editColumn('transaction_channel', function (Sales $sales) use ($transactionChannels) {
            return '<small class="label bg-'. $transactionChannels[$sales->transaction_channel]['label-color'] . '">'
            . '<i class="'. $transactionChannels[$sales->transaction_channel]['icon'] . '" style="margin-right: 5px;"></i>'
            . $transactionChannels[$sales->transaction_channel]['label'] .
            '</small>';
          })
          ->editColumn('order_date', function (Sales $sales) {
            $roleUser = request()->user()->role->name;
            $isSuperAdmin = $roleUser === 'super_admin';

            return '<a class="has-ajax-form text-red" href=""
            data-toggle="modal"
            data-target="#ajax-form"
            data-form-url="' . url($this->route) . '"
            data-load="'. url($this->route . '/' . $sales->id . '/ajax-form') . '"
            data-is-superadmin="'. $isSuperAdmin . '">
            ' . $sales->order_date->format('m/d/Y') . ' - ' . $sales->id . '
            </a>';
          })
          ->editColumn('quotation_number', function (Sales $sales) {
            return '<a class="text-red"
            target="_blank"
            href="'. url('/sales/quotation/' . $sales->id . '/edit') . '">
            ' . $sales->quotation_number . '
            </a>';
          })
          ->addColumn('action', function (Sales $sales) {
            return \TransAction::table($this->route, $sales, null, $sales->order_log_print);
          })
          ->rawColumns(['order_date', 'quotation_number', 'order_status', 'transaction_channel', 'action'])
          ->make(true);
        }

        $this->params['model'] = $this->model;
        $this->params['datatable'] = $this->datatablesBuilder
        ->addColumn([ 'data' => 'order_date', 'name' => 'order_date', 'title' => 'Date-No' ])
        ->addColumn([ 'data' => 'order_number', 'name' => 'order_number', 'title' => 'Sales Order No' ])
        ->addColumn([ 'data' => 'pic.name', 'name' => 'pic.name', 'title' => 'PIC' ])
        ->addColumn([ 'data' => 'total_item', 'name' => 'total_item', 'title' => 'Item' ])
        ->addColumn([ 'data' => 'grand_total_price', 'name' => 'grand_total_price', 'title' => 'Grand Total' ])
        ->addColumn([ 'data' => 'order_status', 'name' => 'order_status', 'title' => 'Progress Status' ])
        ->addColumn([ 'data' => 'transaction_channel', 'name' => 'transaction_channel', 'title' => 'Channel' ])
        ->addColumn([ 'data' => 'action', 'name' => 'action', 'title' => 'Action' ])
        ->parameters([
          'initComplete' => 'function() {
            $.getScript("'. asset("js/utomodeck.js") .'");
            $.getScript("'. asset("js/sales/order-index.js") .'");
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
        $this->params['model']['order_number'] = \RunningNumber::generate('sales', 'order_number', \Config::get('transactions.sales_order.code'));

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
        $keepSalesDetails = [];
        $validator = $this->_validate($request->all());

        if($validator->fails())
        {
          dd($validator);
          return redirect()
          ->back()
          ->withErrors($validator)
          ->withInput();
        }

        // check request->id ada atau tidak, kalau tidak, kembalikan

        try {
          DB::beginTransaction();
          $submitAction = $request->submit;
          $salesDetails = $request->sales_details;

          $params = $request->all();
          $params['order_date'] = date('Y-m-d', strtotime($request->order_date));
          $params['order_status'] = $this->model::ORDER_PENDING;
          $params['discount'] = str_replace(',', '', $request->discount);
          $params['downpayment'] = 0;
          $params['quotation_number'] = 0;
          $params['quotation_date'] = date('Y-m-d');


          unset(
            $params['id'],
            $params['submit'],
            $params['_token'],
            $params['SalesDetails']
          );

          $item = $this->model::create($params);

          // $sales = $this->model::where('id', $request->id)->first();
          // $sales->update($params);

          if(!empty($salesDetails) && count($salesDetails) > 0) {
            foreach ($salesDetails as $key => $salesDetail) {
              $itemMaterial = Item::find(str_replace(',', '', $salesDetail['item_material_id']));
              $itemName = $itemMaterial->name;
              // $id = $salesDetail['id'];

              $salesDetail['order_status'] = $this->model::ORDER_PENDING;
              $salesDetail['width'] = 0;
              $salesDetail['height'] = 0;
              $salesDetail['weight'] = 0;
              $salesDetail['quantity'] = str_replace(',', '', $salesDetail['quantity']);
              $salesDetail['item_name'] = $itemName;
              $salesDetail['price'] = str_replace(',', '', $salesDetail['estimation_price']);
              $salesDetail['total_price'] = str_replace(',', '', $salesDetail['total_price']);

              $newSalesDetail = $item->sales_details()->create($salesDetail);


              $keepSalesDetails[] = $newSalesDetail->id;
            }

            // update status request jadi cancel untuk item yang gk ada di request
            // $sales->sales_details()->whereNotIn('id', $keepSalesDetails)->update([
            //   'quotation_status' => $this->model::QUOTATION_REJECT
            // ]);
          }

          //check jika menggunakan dp, create ar untuk dp
          // if($sales->downpayment > 0) {
          //   $profileTransactionSetting = $sales->customer->profile->transaction_setting;

          //   if($sales->downpayment < $profileTransactionSetting->minimum_downpayment) {
          //     $request->session()->flash('notif', [
          //       'code' => 'failed ' . __FUNCTION__ . 'd',
          //       'message' => 'minimum downpayment: ' . $profileTransactionSetting->minimum_downpayment
          //     ]);

          //     return redirect()
          //     ->back()
          //     ->withInput();
          //   }

          //   $arDP = AccountReceivable::create([
          //     'sales_id' => $sales->id,
          //     'amount' => $sales->downpayment,
          //     'balance' => $sales->downpayment,
          //     'note' => '',
          //     'type' => AccountReceivable::TYPE_DP
          //   ]);
          // }

          //create ar untuk sisa dari dp jika pakai dp atau ar keseluruhan.
          // $arBalance = $params['grand_total_price'] - $request->downpayment;

          // $arBill = AccountReceivable::create([
          //   'sales_id' => $sales->id,
          //   'amount' => $arBalance,
          //   'balance' => $arBalance,
          //   'note' => '',
          //   'type' => AccountReceivable::TYPE_BILL
          // ]);

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

        $transactionChannels = $this->params['transaction_channels'];

        if ($request->ajax()) {
          $query = $this->model::with(['sales_details', 'pic', 'customer'])
          ->where('quotation_status', $this->model::QUOTATION_ACCEPT)
          ->whereNotNull('order_number');
          if ($request->filled("periode_awal") && $request->filled("periode_akhir")) {
            $query = $query->whereBetween("quotation_date", [Carbon::parse($request->periode_awal)->startOfDay(), Carbon::parse($request->periode_akhir)->endOfDay()]);
          }
          if($request->filled("filter_so_no")) {
            $query = $query->where("order_number", "like", "%$request->filter_so_no%");
          }
          if($request->filled("filter_sq_no")) {
            $query = $query->where("quotation_number", "like", "%$request->filter_sq_no%");
          }
          if($request->filled("filter_pic")) {
            $query = $query->whereHas("pic", function($q) use($request) {
              $q->where("name", "like", "%$request->filter_pic%");
            });
          }
          if($request->filled("filter_status")) {
            $query = $query->where("order_status", $request->filter_status);
          }

          return Datatables::of($query)
          ->addColumn('total_item', function (Sales $sales) {
            return $sales->sales_details->count();
          })
          ->editColumn('grand_total_price', function (Sales $sales) {
            return \Rupiah::format($sales->grand_total_price);
          })
          ->editColumn('order_status', function (Sales $sales) use ($orderStatus) {
            return '<small class="label bg-'. $orderStatus[$sales->order_status]['label-color'] . '">' . $orderStatus[$sales->order_status]['label'] . '</small>';
          })
          ->editColumn('transaction_channel', function (Sales $sales) use ($transactionChannels) {
            return '<small class="label bg-'. $transactionChannels[$sales->transaction_channel]['label-color'] . '">'
            . '<i class="'. $transactionChannels[$sales->transaction_channel]['icon'] . '" style="margin-right: 5px;"></i>'
            . $transactionChannels[$sales->transaction_channel]['label'] .
            '</small>';
          })
          ->editColumn('order_date', function (Sales $sales) {
            $roleUser = request()->user()->role->name;
            $isSuperAdmin = $roleUser === 'super_admin';

            return '<a class="has-ajax-form text-red" href=""
            data-toggle="modal"
            data-target="#ajax-form"
            data-form-url="' . url($this->route) . '"
            data-load="'. url($this->route . '/' . $sales->id . '/ajax-form') . '"
            data-is-superadmin="'. $isSuperAdmin . '">
            ' . $sales->order_date->format('m/d/Y') . ' - ' . $sales->id . '
            </a>';
          })
          ->editColumn('quotation_number', function (Sales $sales) {
            return '<a class="text-red"
            target="_blank"
            href="'. url('/sales/quotation/' . $sales->id . '/edit') . '">
            ' . $sales->quotation_number . '
            </a>';
          })
          ->addColumn('action', function (Sales $sales) {
            return \TransAction::table($this->route, $sales, null, $sales->order_log_print);
          })
          ->rawColumns(['order_date', 'quotation_number', 'order_status', 'transaction_channel', 'action'])
          ->make(true);
        }

        $this->params['model'] = $this->model;
        $this->params['datatable'] = $this->datatablesBuilder
        ->addColumn([ 'data' => 'order_date', 'name' => 'order_date', 'title' => 'Date-No' ])
        ->addColumn([ 'data' => 'order_number', 'name' => 'order_number', 'title' => 'Sales Order No' ])
        ->addColumn([ 'data' => 'quotation_number', 'name' => 'quotation_number', 'title' => 'Sales Request No' ])
        ->addColumn([ 'data' => 'pic.name', 'name' => 'pic.name', 'title' => 'PIC' ])
        ->addColumn([ 'data' => 'total_item', 'name' => 'total_item', 'title' => 'Item' ])
        ->addColumn([ 'data' => 'grand_total_price', 'name' => 'grand_total_price', 'title' => 'Grand Total' ])
        ->addColumn([ 'data' => 'order_status', 'name' => 'order_status', 'title' => 'Progress Status' ])
        ->addColumn([ 'data' => 'transaction_channel', 'name' => 'transaction_channel', 'title' => 'Channel' ])
        ->addColumn([ 'data' => 'action', 'name' => 'action', 'title' => 'Action' ])
        ->parameters([
          'initComplete' => 'function() {
            $.getScript("'. asset("js/utomodeck.js") .'");
            $.getScript("'. asset("js/sales/order-index.js") .'");
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
            $params['order_date'] = date('Y-m-d', strtotime($request->order_date));
            $params['order_status'] = $this->model::ORDER_PENDING;
            $params['discount'] = str_replace(',', '', $request->discount);
            $params['downpayment'] = str_replace(',', '', $request->downpayment);

            unset(
              $params['id'],
              $params['quotation_number'],
              $params['submit'],
              $params['_token'],
              $params['salesDetails']
            );

            $sales = $this->model->where('id', $id)->first();
            $sales->update($params);

            if(!empty($salesDetails) && count($salesDetails) > 0) {
              foreach ($salesDetails as $key => $salesDetail) {
                $id = $salesDetail['id'];

                $salesDetail['order_status'] = $this->model::ORDER_PENDING;
                $salesDetail['quantity'] = str_replace(',', '', $salesDetail['quantity']);
                $salesDetail['price'] = str_replace(',', '', $salesDetail['price']);
                $salesDetail['total_price'] = str_replace(',', '', $salesDetail['total_price']);

                $currentSalesDetail = $sales->sales_details()->where('id', $id)->first();

                if(!empty($currentSalesDetail)) {
                  $currentSalesDetail->update($salesDetail);
                  $keepSalesDetails[] = $currentSalesDetail->id;
                  continue;
                }

                $newSalesDetail = $sales->sales_details()->create($salesDetail);
                $keepSalesDetails[] = $newSalesDetail->id;
              }

              /**
              * untuk item yang gk ada di request,
              * update status order jadi cancel (kalau submit= save_print, karena berikutnya
              * tidak mungkin di update lagi)
              * update status order jadi pending (kalau submit= save, karena ada kemungkinan di update lagi)
              *   */
              $sales->sales_details()->whereNotIn('id', $keepSalesDetails)->update([
                'order_status' => ($submitAction == 'save_print') ? $this->model::ORDER_CANCEL : $this->model::ORDER_PENDING
              ]);
            }

            // harusnya ada update ar juga

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
            $orderStatus = $this->model::ORDER_PENDING;
            $sales = $this->model->where('id', $id)->first();

            if($sales->order_status == $this->model::ORDER_PROCESS) {
              $orderStatus = $this->model::ORDER_CANCEL;
              $sales->order_log_print()->delete();
            }

            $sales->sales_details()->update([ 'order_status' => $orderStatus ]);
            $sales->update([
              'order_date' => null,
              'order_number' => null,
              'order_status' => $orderStatus
            ]);

            $sales->account_receivables()->where('balance', '>', 0)->delete();

            DB::commit();
            return response()->json([], 204);
          } catch (\Throwable $th) {
            DB::rollback();
            return response()->json(['message' => $th->getMessage()], 500);
          }
        }

        // check paylater limit balance
        private function _checkLimitAvailable ($profile)
        {
          $accountPayables = AccountReceivable::join('sales', 'sales.id', '=', 'account_receivables.sales_id')
          ->join('shippings', 'shippings.sales_id', '=', 'sales.id')
          ->join('sales_invoices', 'sales_invoices.shipping_id', '=', 'shippings.id')
          ->where('sales_invoices.payment_method_id', 4)
          ->where('sales.customer_id', $profile->user_id)
          ->where('balance', '>', 0)
          ->select('account_receivables.*')
          ->distinct()
          ->get();

          if(empty($accountPayables)) return 0;
          $limitUsed = $accountPayables->sum('balance');

          return $profile->transaction_setting->limit - $limitUsed;
        }

        private function _getUnpaidAR($sales)
        {
          $unpaidAR = 0;

          if($sales->transaction_channel == $this->model::TRANSACTION_CHANNEL_WEB) {
            if(!empty($sales->downpayment)) {
              $unpaidDP = $sales->account_receivables()
              ->where('balanc', '>', 0)
              ->where('account_receivables.type', AccountReceivable::TYPE_DP)
              ->select('account_receivables.*')
              ->distinct()
              ->get();

              $unpaidAR = $unpaidDP->sum('balance');
            }else {
              $listAR = $sales->account_receivables()
              ->where('balance', '>', 0)
              ->select('account_receivables.*')
              ->distinct()
              ->get();

              $unpaidAR = $listAR->sum('balance');
            }
          }else {
            if(!empty($sales->downpayment)) {
              $unpaidDP = $sales->account_receivables()
              ->join('shipping_instructions', 'shipping_instructions.sales_id', '=', 'account_receivables.sales_id')
              ->join('delivery_notes', 'delivery_notes.shipping_instruction_id', '=', 'shipping_instructions.id')
              ->join('sales_invoices', 'sales_invoices.delivery_note_id', '=', 'delivery_notes.id')
              ->where('sales_invoices.payment_method_id', '<>', 4)
              ->where('balance', '>', 0)
              ->where('account_receivables.type', AccountReceivable::TYPE_DP)
              ->select('account_receivables.*')
              ->distinct()
              ->get();

              $unpaidAR = $unpaidDP->sum('balance');
            }else {
              $listAR = $sales->account_receivables()
              ->join('shipping_instructions', 'shipping_instructions.sales_id', '=', 'account_receivables.sales_id')
              ->join('delivery_notes', 'delivery_notes.shipping_instruction_id', '=', 'shipping_instructions.id')
              ->join('sales_invoices', 'sales_invoices.delivery_note_id', '=', 'delivery_notes.id')
              ->where('sales_invoices.payment_method_id', '<>', 4)
              ->where('balance', '>', 0)
              ->select('account_receivables.*')
              ->distinct()
              ->get();

              $unpaidAR = $listAR->sum('balance');
            }
          }

          return $unpaidAR;
        }

        public function print(Request $request, $id)
        {
          $roleUser = request()->user()->role->name;
          $isSuperAdmin = $roleUser === 'super_admin';
          $unpaidAR = 0;

          try {
            DB::beginTransaction();
            $sales = $this->model->where('id', $id)->with([
              'sales_details', 'customer','pic', 'customer.profile' , 'customer.profile.default_address',
              'customer.profile.default_address.region_city', 'customer.profile.default_address.region_district'])->first();
            $params['model'] = $sales;

            // $params['shippingMethod'] = new ShippingInstruction();
            // $params['shippingMethods'] = $this->params['shippingMethods'];

            // if(!empty($sales->order_log_print)) {
            //   if($isSuperAdmin) return \PrintFile::original($this->routeView . '.pdf', $params, 'Sales-Order-' . $sales->order_number);
            //   //print with watermark
            //   return \PrintFile::copy($this->routeView . '.pdf', $params, 'Sales-Order-' . $sales->order_number);
            // }

            // $unpaidAR = $this->_getUnpaidAR($sales);
            // if($unpaidAR > 0) {
            //   $request->session()->flash('notif', [
            //     'code' => 'failed ' . __FUNCTION__ . 'd',
            //     'message' => 'Tidak dapat melakukan print, terdapat order lain yang belum dibayar / belum lunas.'
            //   ]);

            //   return redirect()
            //   ->back()
            //   ->withInput();
            // }

            // if($unpaidAR > $this->_checkLimitAvailable($sales->customer->profile)) {
            //     $request->session()->flash('notif', [
            //         'code' => 'failed ' . __FUNCTION__ . 'd',
            //         'message' => 'Tidak dapat melakukan print, melebihi batas limit.'
            //     ]);

            //     return redirect()
            //         ->back()
            //         ->withInput();
            // };

            //print without watermark
            LogPrint::create([
              'transaction_code' => \Config::get('transactions.sales_order.code'),
              'transaction_number' => $sales->order_number,
              'employee_id' => Auth()->user()->id,
              'date' => now()
            ]);

            $sales->sales_details()
            ->where(['order_status' => $this->model::ORDER_PENDING])
            ->update(['order_status' => $this->model::ORDER_PROCESS]);
            $sales->order_status = $this->model::ORDER_PROCESS;
            $sales->save();

            DB::commit();
            // return view($this->routeView . '.pdf', $params);

            return \PrintFile::original($this->routeView . '.pdf', $params, 'Sales-Order-' . $sales->order_number);
          } catch (\Throwable $th) {
            DB::rollback();

            // dd($th);

            $request->session()->flash('notif', [
              'code' => 'failed ' . __FUNCTION__ . 'd',
              'message' => str_replace(".", " ", $this->routeView) . ' : ' . $th->getMessage() . ' ' . $th->getLine(),
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
            // 'quotation_number' => ['required'],
            'customer_id' => ['required'],
            'payment_method_id' => ['required'],
            'warehouse_id' => ['required'],
            'created_by' => ['required'],
            'order_number' => ['required', 'unique:sales,order_number' . $ignoredId],
            'order_date' => ['required'],
            'sales_details.*.quantity' => ['required'],
            'sales_details.*.estimation_price' => ['required'],
            'sales_details.*.total_price' => ['required']
          ]);
        }

        public function export(Request $request)
        {
          $model = $this->model::with(['sales_details', 'pic', 'customer'])
          ->where('quotation_status', $this->model::QUOTATION_ACCEPT)
          ->whereNotNull('order_number');
          if($request->filled("filter_date")) {
            $query = $query->whereDate("order_date", $request->filter_date);
          }
          if($request->filled("filter_so_no")) {
            $query = $query->where("order_number", "like", "%$request->filter_so_no%");
          }
          if($request->filled("filter_sq_no")) {
            $query = $query->where("quotation_number", "like", "%$request->filter_sq_no%");
          }
          if($request->filled("filter_pic")) {
            $query = $query->whereHas("pic", function($q) use($request) {
              $q->where("name", "like", "%$request->filter_pic%");
            });
          }
          if($request->filled("filter_status")) {
            $query = $query->where("order_status", $request->filter_status);
          }

          $heading = $this->model->getTableColumns();
          return Excel::download(new SalesOrderExport($model, $heading), 'salesorder.xlsx');
        }
      }
