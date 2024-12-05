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
use App\Models\Shipping\ShippingInstruction;
use App\Models\Shipping\DeliveryNote;
use App\Models\Sales\SalesInvoice;
use App\Models\Master\Item\ItemMaterial;
use App\Models\Master\Material\RawMaterial;
use App\Models\Master\Payment\PaymentMethod;
use Carbon\Carbon;

class SalesInvoiceController extends Controller
{
  private $route = 'sales/invoice';
  private $routeView = 'sales.invoice';
  private $params = [];

  public function __construct (Builder $datatablesBuilder)
  {
    $this->model = new SalesInvoice();
    $this->datatablesBuilder = $datatablesBuilder;

    $this->params['route'] = $this->route;
    $this->params['routeView'] = $this->routeView;
    $this->params['shippingMethods'] = [
      ShippingInstruction::METHOD_IS_PICKUP => ['label' => 'pickup', 'label-color' => 'green'],
      ShippingInstruction::METHOD_IS_PICKUP_POINT => ['label' => 'pickup pada pickup point', 'label-color' => 'yellow'],
      ShippingInstruction::METHOD_IS_DELIVERY => ['label' => 'kirim', 'label-color' => 'red'],
    ];
    $this->params['paymentMethods'] = PaymentMethod::active()->where('available_at', 0)->select('id', 'name', 'image')->get();
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
    $result = $this->model->where('id', $id)->with([
      'sales_details',
      'quotation_log_print' => function ($query) {
        $query->with(['employee']);
      }
      ])->first();

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
      $invoiceStatus = [
        $this->model::PENDING => ['label' => 'pending', 'label-color' => 'yellow'],
        $this->model::BILLED => ['label' => 'di tagihkan', 'label-color' => 'blue'],
        $this->model::PAID_OFF => ['label' => 'terbayar', 'label-color' => 'green'],
      ];

      if ($request->ajax()) {
        return Datatables::of($this->model::whereNotNull('number')->with(['delivery_note', 'delivery_note.shipping_instruction', 'delivery_note.shipping_instruction.sales']))
        ->editColumn('total_bill', function (SalesInvoice $salesInv) {
          return \Rupiah::format($salesInv->total_bill);
        })
        ->editColumn('status', function (SalesInvoice $salesInv) use ($invoiceStatus) {
          return '<small class="label bg-'. $invoiceStatus[$salesInv->status]['label-color'] . '">' . $invoiceStatus[$salesInv->status]['label'] . '</small>';
        })
        ->editColumn('created_at', function (SalesInvoice $salesInv) {
          return '<a class="has-ajax-form text-red" href=""
          data-toggle="modal"
          data-target="#ajax-form"
          data-form-url="' . url($this->route) . '"
          data-load="'. url($this->route . '/' . $salesInv->id . '/ajax-form') . '">
          ' . $salesInv->created_at->format('m/d/Y') . ' - ' . $salesInv->id . '
          </a>';
        })
        ->editColumn('due_date', function (SalesInvoice $salesInv) {
          return '<a class="has-ajax-form text-red" href=""
          data-toggle="modal"
          data-target="#ajax-form"
          data-form-url="' . url($this->route) . '"
          data-load="'. url($this->route . '/' . $salesInv->id . '/ajax-form') . '">
          ' . $salesInv->due_date->format('m/d/Y') . ' - ' . $salesInv->id . '
          </a>';
        })
        ->editColumn('paid_of_date', function (SalesInvoice $salesInv) {
          return '<a class="has-ajax-form text-red" href=""
          data-toggle="modal"
          data-target="#ajax-form"
          data-form-url="' . url($this->route) . '"
          data-load="'. url($this->route . '/' . $salesInv->id . '/ajax-form') . '">
          ' . optional($salesInv->paid_of_date)->format('m/d/Y') . ' - ' . $salesInv->id . '
          </a>';
        })
        ->addColumn('delivery_number', function (SalesInvoice $salesInv) {
          return '<a class="text-red"
          target="_blank"
          href="'. url('/sales/delivery-note/' . $salesInv->delivery_note_id . '/edit') . '">
          ' . $salesInv->delivery_note->number . '
          </a>';
        })
        ->addColumn('action', function (SalesInvoice $salesInv) {
          return \TransAction::table($this->route, $salesInv, null, $salesInv->log_print);
        })
        ->rawColumns(['delivery_number', 'created_at', 'due_date', 'paid_of_date', 'status', 'action'])
        ->make(true);
      }

      $this->params['model'] = $this->model;
      $this->params['datatable'] = $this->datatablesBuilder
      ->addColumn([ 'data' => 'created_at', 'name' => 'created_at', 'title' => 'Date-No' ])
      ->addColumn([ 'data' => 'delivery_number', 'name' => 'delivery_number', 'title' => 'Delivery Note No' ])
      ->addColumn([ 'data' => 'due_date', 'name' => 'due_date', 'title' => 'Due Date Inv.' ])
      ->addColumn([ 'data' => 'total_bill', 'name' => 'total_bill', 'title' => 'Amount' ])
      ->addColumn([ 'data' => 'status', 'name' => 'status', 'title' => 'Inv. Status' ])
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
      $this->params['model']->invoice_details = [];
      $this->params['model']['number'] = \RunningNumber::generate('sales_invoices', 'number', \Config::get('transactions.sales_invoice.code'));

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
        // $salesDetails = $request->invoice_details;

        $deliveryNote = DeliveryNote::find($request->delivery_number);

        $params = $request->all();
        $params['delivery_note_id'] = $deliveryNote->id;
        $params['due_date'] = date('Y-m-d', strtotime($request->due_date));
        $params['created_at'] = date('Y-m-d', strtotime($request->created_at));
        $params['status'] = $this->model::PENDING;
        $params['total_quantity'] = $deliveryNote->delivery_note_details()->sum('quantity');
        $params['total_bill'] = $request->total_bill;

        unset(
          $params['submit'],
          $params['_token'],
          $params['delivery_number'],
          $params['customer_id'],
          $params['shipping_method_id'],
          $params['address_id'],
          $params['shipping_cost'],
          $params['discount'],
          $params['invoice_details']
        );

        $deliveryNote = DeliveryNote::find($params['delivery_note_id']);
        $sales = $deliveryNote->shipping_instruction->sales;

        if($sales->transaction_channel == Sales::TRANSACTION_CHANNEL_WEB) {
          $salesInv = $this->model::create($params);
        } else {
          $salesInvId = SalesInvoice::where('delivery_note_id', $deliveryNote->id)->first()->id;

          unset($params['status']);

          $this->model::where('id', $salesInvId)->update($params);
          $salesInv = $this->model::find($salesInvId);
        }

        // if(!empty($salesDetails) && count($salesDetails) > 0) {
        //     foreach ($salesDetails as $key => $salesDetail) {
        //         $itemMaterial = ItemMaterial::find($salesDetail['item_material_id']);
        //         $item = $itemMaterial->item;

        //         unset($salesDetail['length_selected']);

        //         $salesDetail['quotation_status'] = $this->model::QUOTATION_PENDING;
        //         $salesDetail['order_status'] = $this->model::DEFAULT_ORDER_STATUS;
        //         $salesDetail['is_custom_length'] = $salesDetail['is_custom_length'] == 'true' ? 1 : 0;
        //         $salesDetail['width'] = $item->width;
        //         $salesDetail['height'] = $item->height;
        //         $salesDetail['weight'] = $item->weight*$salesDetail['length'];
        //         $salesDetail['quantity'] = str_replace(',', '', $salesDetail['quantity']);
        //         $salesDetail['price'] = str_replace(',', '', $salesDetail['price']);
        //         $salesDetail['total_price'] = str_replace(',', '', $salesDetail['total_price']);
        //         // belum hitung kalau custom length

        //         $sales->sales_details()->create($salesDetail);
        //     }
        // }

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
    public function show(Request $request,$id)
    {
      //
      $invoiceStatus = [
        $this->model::PENDING => ['label' => 'pending', 'label-color' => 'yellow'],
        $this->model::BILLED => ['label' => 'di tagihkan', 'label-color' => 'blue'],
        $this->model::PAID_OFF => ['label' => 'terbayar', 'label-color' => 'green'],
      ];

      if ($request->ajax()) {
        $query = $this->model::whereNotNull('number')->with(['delivery_note', 'delivery_note.shipping_instruction', 'delivery_note.shipping_instruction.sales']);
        if ($request->filled("periode_awal") && $request->filled("periode_akhir")) {
          $query = $query->whereBetween("created_at", [Carbon::parse($request->periode_awal)->startOfDay(), Carbon::parse($request->periode_akhir)->endOfDay()]);
        }
        if($request->filled("filter_deliv_no")) {
          $query = $query->whereHas("delivery_note", function($q) use($request) {
            $q->where("number", "like", "%$request->filter_deliv_no%");
          });
        }
        if($request->filled("filter_due")) {
          $query = $query->whereDate("due_date", $request->filter_due);
        }
        if($request->filled("filter_status")) {
          $query = $query->where("status", $request->filter_status);
        }
        return Datatables::of($query)
        ->editColumn('total_bill', function (SalesInvoice $salesInv) {
          return \Rupiah::format($salesInv->total_bill);
        })
        ->editColumn('status', function (SalesInvoice $salesInv) use ($invoiceStatus) {
          return '<small class="label bg-'. $invoiceStatus[$salesInv->status]['label-color'] . '">' . $invoiceStatus[$salesInv->status]['label'] . '</small>';
        })
        ->editColumn('created_at', function (SalesInvoice $salesInv) {
          return '<a class="has-ajax-form text-red" href=""
          data-toggle="modal"
          data-target="#ajax-form"
          data-form-url="' . url($this->route) . '"
          data-load="'. url($this->route . '/' . $salesInv->id . '/ajax-form') . '">
          ' . $salesInv->created_at->format('m/d/Y') . ' - ' . $salesInv->id . '
          </a>';
        })
        ->editColumn('due_date', function (SalesInvoice $salesInv) {
          return '<a class="has-ajax-form text-red" href=""
          data-toggle="modal"
          data-target="#ajax-form"
          data-form-url="' . url($this->route) . '"
          data-load="'. url($this->route . '/' . $salesInv->id . '/ajax-form') . '">
          ' . $salesInv->due_date->format('m/d/Y') . ' - ' . $salesInv->id . '
          </a>';
        })
        ->editColumn('paid_of_date', function (SalesInvoice $salesInv) {
          return '<a class="has-ajax-form text-red" href=""
          data-toggle="modal"
          data-target="#ajax-form"
          data-form-url="' . url($this->route) . '"
          data-load="'. url($this->route . '/' . $salesInv->id . '/ajax-form') . '">
          ' . optional($salesInv->paid_of_date)->format('m/d/Y') . ' - ' . $salesInv->id . '
          </a>';
        })
        ->addColumn('delivery_number', function (SalesInvoice $salesInv) {
          return '<a class="text-red"
          target="_blank"
          href="'. url('/sales/delivery-note/' . $salesInv->delivery_note_id . '/edit') . '">
          ' . $salesInv->delivery_note->number . '
          </a>';
        })
        ->addColumn('action', function (SalesInvoice $salesInv) {
          return \TransAction::table($this->route, $salesInv, null, $salesInv->log_print);
        })
        ->rawColumns(['delivery_number', 'created_at', 'due_date', 'paid_of_date', 'status', 'action'])
        ->make(true);
      }

      $this->params['model'] = $this->model;
      $this->params['datatable'] = $this->datatablesBuilder
      ->addColumn([ 'data' => 'created_at', 'name' => 'created_at', 'title' => 'Date-No' ])
      ->addColumn([ 'data' => 'delivery_number', 'name' => 'delivery_number', 'title' => 'Delivery Note No' ])
      ->addColumn([ 'data' => 'due_date', 'name' => 'due_date', 'title' => 'Due Date Inv.' ])
      ->addColumn([ 'data' => 'total_bill', 'name' => 'total_bill', 'title' => 'Amount' ])
      ->addColumn([ 'data' => 'status', 'name' => 'status', 'title' => 'Inv. Status' ])
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
    public function destroy($id)
    {
      try {
        DB::beginTransaction();
        $invoice = $this->model->find($id);
        $invoice->log_print()->delete();
        $invoice->delete();

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
        $salesInvoiceStatus = $this->model::BILLED;
        $salesInv = $this->model->find($id);
        $sales = $salesInv->delivery_note->shipping_instruction->sales;
        $params['model'] = $salesInv;
        $params['shippingMethod'] = new ShippingInstruction();

        if(!empty($salesInv->log_print)) {
          //print with watermark
          return \PrintFile::copy($this->routeView . '.pdf', $params, 'Sales-Invoice-' . $salesInv->number);
        }

        //print without watermark
        LogPrint::create([
          'transaction_code' => \Config::get('transactions.sales_invoice.code'),
          'transaction_number' => $salesInv->number,
          'employee_id' => Auth()->user()->id,
          'date' => now()
        ]);

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

        if($unpaidAR == 0) $salesInvoiceStatus = $this->model::PAID_OFF;

        $salesInv->status = $salesInvoiceStatus;
        $salesInv->save();

        DB::commit();
        return \PrintFile::original($this->routeView . '.pdf', $params, 'Sales-Invoice-' . $salesInv->number);
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
        'number' => ['required', 'unique:sales_invoices,number' . $ignoredId],
        'due_date' => ['required'],
        'payment_method_id' => ['required']
      ]);
    }
  }
