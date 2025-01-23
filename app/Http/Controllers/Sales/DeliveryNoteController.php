<?php

namespace App\Http\Controllers\Sales;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;
use Yajra\DataTables\DataTables;
use Yajra\DataTables\Html\Builder;
use Barryvdh\DomPDF\Facade\Pdf;

use App\Models\LogPrint;
use App\Models\Shipping\ShippingInstruction;
use App\Models\Shipping\DeliveryNote;
use App\Models\Shipping\DeliveryNoteDetail;
use App\Models\Sales\SalesInvoice;
use App\Models\Sales\Sales;
use App\Models\Master\Item\ItemMaterial;
use App\Models\Master\Material\RawMaterial;
use App\Models\Production\GoodReceipt;
use App\Models\Production\JobOrder;
use Carbon\Carbon;

class DeliveryNoteController extends Controller
{
  private $route = 'sales/delivery-note';
  private $routeView = 'sales.delivery-note';
  private $params = [];

  public function __construct (Builder $datatablesBuilder)
  {
    $this->model = new DeliveryNote();
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

  private function _getPaymentInformation($sales, $deliveryNote)
  {
    $payment = $sales;

    if($sales->transaction_channel == $sales::TRANSACTION_CHANNEL_MOBILE) {
      $payment = SalesInvoice::where('delivery_note_id', $deliveryNote->id)->first();
    }

    return $payment;
  }

  public function search(Request $request)
  {
    $where = "status = " . $this->model::DELIVERY_PROCESS;
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

  public function searchById ($id)
  {
    $result = $this->model->where('id', $id)->with(['job_order',
      'delivery_note_details', 'delivery_note_details.job_order_detail', 'delivery_note_details.job_order_detail.item_material',
      'log_print' => function ($query) {
        $query->with(['employee']);
      }
      ])->first();

      foreach ($result->delivery_note_details as $deliveryNoteDetail) {
        // dd($deliveryNoteDetail->job_order_detail);
        $shippingDetail = $deliveryNoteDetail->job_order_detail;
        $itemMaterial = $shippingDetail->item_material;

        $deliveryNoteDetail->item_material_id = $itemMaterial->id;
        $deliveryNoteDetail->item_name = $itemMaterial->name;
        $deliveryNoteDetail->price = $shippingDetail->price;
        $deliveryNoteDetail->total_price = $deliveryNoteDetail->price*$deliveryNoteDetail->quantity;

        unset(
          $shippingDetail['status'],
          $shippingDetail['created_at'],
          $shippingDetail['updated_at'],
          $shippingDetail['deleted_at'],
          $shippingDetail['shipping_instruction_detail_id'],
          $shippingDetail['shipping_instruction_detail']
        );
      }

      // dd($result);

      $result->vendor_id = $result->job_order->vendor_id;
      // $result->customer_id = $result->shipping_instruction->sales->customer_id;
      // $result->discount = $this->_getPaymentInformation($result->shipping_instruction->sales, $result)->discount;
      // $result->payment_method_id = $this->_getPaymentInformation($result->shipping_instruction->sales, $result)->payment_method_id;
      // $result->name = $result->number;
      return response()->json($result, 200);
    }

    /**
    * Display a listing of the resource.
    *
    * @return \Illuminate\Http\Response
    */
    public function index(Request $request)
    {
      // $deliveryStatus = [
      //   $this->model::DELIVERY_PENDING => ['label' => 'pending', 'label-color' => 'yellow'],
      //   $this->model::DELIVERY_PROCESS => ['label' => 'process', 'label-color' => 'blue'],
      //   $this->model::DELIVERY_FINISH => ['label' => 'finish', 'label-color' => 'green'],
      //   $this->model::DELIVERY_RETUR => ['label' => 'retur', 'label-color' => 'red'],
      // ];


      if ($request->ajax()) {
        // dd($this->model::with(['sales', 'sales.customer'])->get());
        $deliveryNotes = $this->model::with([
          'job_order'
        ])
        ->whereHas('delivery_note_details');

        return Datatables::of($deliveryNotes)
        // ->editColumn('status', function (DeliveryNote $shipping) use ($deliveryStatus) {
        //   return '<small class="label bg-'. $deliveryStatus[$shipping->status]['label-color'] . '">' . $deliveryStatus[$shipping->status]['label'] . '</small>';
        // })
        ->editColumn('date', function (DeliveryNote $shipping) {
          return '<a class="has-ajax-form text-red" href=""
          data-toggle="modal"
          data-target="#ajax-form"
          data-form-url="' . url($this->route) . '"
          data-load="'. url($this->route . '/' . $shipping->id . '/ajax-form') . '">
          ' . $shipping->date->format('m/d/Y') . ' - ' . $shipping->id . '
          </a>';
        })
        ->addColumn('action', function (DeliveryNote $shipping) {
          $actionFinish = '';

          // if(
          //   ($shipping->status == $shipping::DELIVERY_PROCESS) //&&
          //   //(!empty($shipping->sales_invoice) && ($shipping->sales_invoice->status == $shipping->sales_invoice::PAID_OFF))
          // ) {
          //   $actionFinish = '<button
          //   class="confirmation-delivered btn btn-default text-green"
          //   style="margin-top: 10px; display: block"
          //   data-target="' . url($this->route . '/' . $shipping->id . '/finish') . '"
          //   data-token="' . csrf_token() . '">
          //   <i class="fa fa-check-circle-o"></i> Finish
          //   </button>';
          // }

          return \TransAction::table($this->route, $shipping, null, $shipping->log_print) . $actionFinish;
        })
        ->rawColumns(['date', 'order_number', 'shipping_method_id', 'action'])
        ->make(true);
      }

      $this->params['model'] = $this->model;
      $this->params['datatable'] = $this->datatablesBuilder
      ->addColumn([ 'data' => 'date', 'name' => 'date', 'title' => 'Date-No' ])
      ->addColumn([ 'data' => 'number', 'name' => 'number', 'title' => 'Delivery Note No' ])
      ->addColumn([ 'data' => 'job_order.number', 'name' => 'job_order.number', 'title' => 'SPK No' ])
      // ->addColumn([ 'data' => 'status', 'name' => 'status', 'title' => 'BAP Status' ])
      ->addColumn([ 'data' => 'action', 'name' => 'action', 'title' => 'Action' ])
      ->parameters([
        'initComplete' => 'function() {
          $.getScript("'. asset("js/utomodeck.js") .'");
          $.getScript("'. asset("js/sales/delivery-index.js") .'");
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
      $this->params['model']['number'] = \RunningNumber::generate('delivery_notes', 'number', \Config::get('transactions.delivery_note.code'));

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

      // dd($request->all());

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
        $deliveryNoteDetails = $request->delivery_note_details;

        $params = $request->all();
        $params['date'] = date('Y-m-d', strtotime($request->date));
        $params['status'] = $this->model::DEFAULT_DELIVERY_STATUS;

        unset(
          $params['submit'],
          $params['_token'],
          $params['vendor_id'],
          $params['job_order_details'],
        );


        $deliveryNote = $this->model::create($params);
        

        if(!empty($deliveryNoteDetails) && count($deliveryNoteDetails) > 0) {
          foreach ($deliveryNoteDetails as $key => $deliveryNoteDetail) {
            

            // dd($deliveryNoteDetail);

            $deliveryNoteDetail['status'] = DeliveryNoteDetail::DEFAULT_DELIVERY_STATUS;
            $deliveryNoteDetail['job_order_detail_id'] = $deliveryNoteDetail['id'];

            $deliveryNote->delivery_note_details()->create($deliveryNoteDetail);
          }
        }

        if($submitAction == 'save_print') {
          $redirectOnSuccess .= "?print=" .$deliveryNote->id;
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
      $deliveryStatus = [
        $this->model::DELIVERY_PENDING => ['label' => 'pending', 'label-color' => 'yellow'],
        $this->model::DELIVERY_PROCESS => ['label' => 'process', 'label-color' => 'blue'],
        $this->model::DELIVERY_FINISH => ['label' => 'finish', 'label-color' => 'green'],
        $this->model::DELIVERY_RETUR => ['label' => 'retur', 'label-color' => 'red'],
      ];

      $pickupMethods = $this->params['shippingMethods'];

      if ($request->ajax()) {
        $deliveryNotes = $this->model::with([
          'shipping_instruction',
          'shipping_instruction.sales',
          'shipping_instruction.sales.customer'
        ])
        ->whereHas('delivery_note_details');
        if ($request->filled("periode_awal") && $request->filled("periode_akhir")) {
          $deliveryNotes = $deliveryNotes->whereBetween("date", [Carbon::parse($request->periode_awal)->startOfDay(), Carbon::parse($request->periode_akhir)->endOfDay()]);
        }
        if($request->filled("filter_deliv_no")) {
          $deliveryNotes = $deliveryNotes->where("number", "like", "%$request->filter_deliv_no%");
        }
        if($request->filled("filter_ship_no")) {
          $deliveryNotes = $deliveryNotes->whereHas("shipping_instruction", function($q) use($request) {
            $q->where("number", "like", "%$request->filter_ship_no%");
          });
        }
        if($request->filled("filter_customer")) {
          $deliveryNotes = $deliveryNotes->whereHas("shipping_instruction.sales.customer", function($q) use($request) {
            $q->where("name", "like", "%$request->filter_customer%");
          });
        }
        // if($request->filled("filter_status")) {
        //   $deliveryNotes = $deliveryNotes->where("status", $request->filter_status);
        // }
        if($request->filled("filter_method")) {
          $deliveryNotes = $deliveryNotes->where("shipping_method_id", $request->filter_method);
        }

        return Datatables::of($deliveryNotes)
        ->editColumn('total_price', function (DeliveryNote $shipping) {
          return \Rupiah::format($shipping->shipping_instruction->sales->total_price);
        })
        // ->editColumn('status', function (DeliveryNote $shipping) use ($deliveryStatus) {
        //   return '<small class="label bg-'. $deliveryStatus[$shipping->status]['label-color'] . '">' . $deliveryStatus[$shipping->status]['label'] . '</small>';
        // })
        ->editColumn('shipping_method_id', function (DeliveryNote $shipping) use ($pickupMethods) {
          return '<small class="label bg-'. $pickupMethods[$shipping->shipping_method_id]['label-color'] . '">'
          . $pickupMethods[$shipping->shipping_method_id]['label'] .
          '</small>';
        })
        ->editColumn('date', function (DeliveryNote $shipping) {
          return '<a class="has-ajax-form text-red" href=""
          data-toggle="modal"
          data-target="#ajax-form"
          data-form-url="' . url($this->route) . '"
          data-load="'. url($this->route . '/' . $shipping->id . '/ajax-form') . '">
          ' . $shipping->date->format('m/d/Y') . ' - ' . $shipping->id . '
          </a>';
        })
        ->addColumn('order_number', function (DeliveryNote $shipping) {
          return '<a class="text-red"
          target="_blank"
          href="'. url('/sales/shipping-instruction/' . $shipping->shipping_instruction_id . '/edit') . '">
          ' . $shipping->shipping_instruction->number . '
          </a>';
        })
        // ->addColumn('action', function (DeliveryNote $shipping) {
        //   $actionFinish = '';

        //   if(
        //     ($shipping->status == $shipping::DELIVERY_PROCESS) &&
        //     (!empty($shipping->sales_invoice) && ($shipping->sales_invoice->status == $shipping->sales_invoice::PAID_OFF))
        //   ) {
        //     $actionFinish = '<button
        //     class="confirmation-delivered btn btn-default text-green"
        //     style="margin-top: 10px; display: block"
        //     data-target="' . url($this->route . '/' . $shipping->id . '/finish') . '"
        //     data-token="' . csrf_token() . '">
        //     <i class="fa fa-check-circle-o"></i> Finish
        //     </button>';
        //   }

        //   return \TransAction::table($this->route, $shipping, null, $shipping->log_print) . $actionFinish;
        // })
        ->rawColumns(['date', 'status', 'order_number', 'shipping_method_id', 'action'])
        ->make(true);
      }

      $this->params['model'] = $this->model;
      $this->params['datatable'] = $this->datatablesBuilder
      ->addColumn([ 'data' => 'date', 'name' => 'date', 'title' => 'Date-No' ])
      ->addColumn([ 'data' => 'number', 'name' => 'number', 'title' => 'Delivery Note No' ])
      ->addColumn([ 'data' => 'status', 'name' => 'status', 'title' => 'Delivery Status' ])
      ->addColumn([ 'data' => 'action', 'name' => 'action', 'title' => 'Action' ])
      ->parameters([
        'initComplete' => 'function() {
          $.getScript("'. asset("js/utomodeck.js") .'");
          $.getScript("'. asset("js/sales/delivery-index.js") .'");
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
      $keepShippingDetails = [];
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
        $shippingDetails = $request->sales_details;

        $params = $request->all();
        $params['date'] = date('Y-m-d', strtotime($request->date));
        $params['status'] = $this->model::NOT_RELEASE;

        unset($params['submit'], $params['_token'], $params['salesDetails']);

        $shipping = $this->model->where('id', $id)->first();
        $shipping->update($params);

        if(!empty($shippingDetails) && count($shippingDetails) > 0) {
          foreach ($shippingDetails as $key => $shippingDetail) {
            $id = $shippingDetail['id'];

            unset($shippingDetail['id']);

            $shippingDetail['quantity'] = str_replace(',', '', $shippingDetail['quantity']);
            $shippingDetail['price'] = str_replace(',', '', $shippingDetail['price']);

            $currentSalesDetail = $shipping->delivery_note_details()->where('id', $id)->first();

            if(!empty($currentSalesDetail)) {
              $currentSalesDetail->update($shippingDetail);
              $keepShippingDetails[] = $currentSalesDetail->id;
              continue;
            }

            $newSalesDetail = $shipping->delivery_note_details()->create($shippingDetail);
            $keepShippingDetails[] = $newSalesDetail->id;
          }

          // hapus yang gk ada di request
          $shipping->delivery_note_details()->whereNotIn('id', $keepShippingDetails)->delete();
        }

        if($submitAction == 'save_print') {
          $redirectOnSuccess .= "?print=" .$shipping->id;
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
    public function finish($id)
    {
      try {
        $salesOrderStatus = Sales::ORDER_PROCESS;

        DB::beginTransaction();
        $deliveryNote = $this->model->find($id);
        $deliveryNote->status = $this->model::DELIVERY_FINISH;
        $deliveryNote->save();

        $deliveryNote->delivery_note_details()->update(['status' => DeliveryNoteDetail::DELIVERY_FINISH]);

        /**
        * check jumlah yang dikirim sama dengan jumlah yang di order atau tidak.
        * kalau ya brrti order sudah selesai.
        * */
        $sales = $deliveryNote->shipping_instruction->sales;
        $hasUndeliveredSales = $sales->sales_details()->whereRaw('(sales_details.quantity - (
          select ifnull(sum(delivery_note_details.quantity), 0)
          from delivery_note_details
          join shipping_instruction_details on shipping_instruction_details.id = delivery_note_details.shipping_instruction_detail_id
          where shipping_instruction_details.sales_detail_id = sales_details.id
          and delivery_note_details.deleted_at is null
          )) > 0')
          ->get();

          if(empty($hasUndeliveredSales)) $salesOrderStatus = Sales::ORDER_FINISH;

          $sales->order_status = $salesOrderStatus;
          $sales->save();

          $sales->sales_details()->update(['order_status' => $salesOrderStatus]);

          DB::commit();
          return response()->json([], 204);
        } 
        catch (\Throwable $th) {
          DB::commit();
          return response()->json([], 204);
          // DB::rollback();
          // return response()->json(['message' => $th->getMessage()], 500);
          
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
          $shipping = $this->model->find($id);
          $shipping->delivery_note_details()->forceDelete();
          $shipping->log_print()->delete();
          $shipping->forceDelete();

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
        $deliveryNote = $this->model->where('id', $id)->with([
            'delivery_note_details',
            'job_order',
            'job_order.job_order_details',
            'job_order.job_order_details.item_material',
            'job_order.pic',
            'job_order.vendor.profile',
            'job_order.vendor.profile.default_address',
            'job_order.vendor.profile.default_address.region_city',
            'job_order.vendor.profile.default_address.region_district'
        ])->first();

        $params['model'] = $deliveryNote;

        // Update status to DELIVERY_FINISH
        $deliveryNote->delivery_note_details()->update(['status' => DeliveryNoteDetail::DELIVERY_FINISH]);
        $deliveryNote->status = $this->model::DELIVERY_PROCESS;
        $deliveryNote->save();

        DB::commit();

        // Set the paper size to B5 (498.9 × 708.66 points)
        $pdf = Pdf::loadView($this->routeView . '.pdf', $params)
        ->setPaper('b5', 'landscape'); // B5 size in points

        // Return the generated PDF
        return $pdf->download('delivery-note-' . $deliveryNote->number . '.pdf');
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
          'created_by' => ['required'],
          'number' => ['required', 'unique:delivery_notes,number' . $ignoredId],
          'date' => ['required'],
          'job_order_details.*.item_material_id' => ['required'],
          'job_order_details.*.quantity' => ['required']
        ]);
      }
    }
