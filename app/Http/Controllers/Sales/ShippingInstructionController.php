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
use App\Models\Shipping\ShippingInstruction;
use App\Models\Sales\Sales;
use App\Models\Purchase\Purchase;
use App\Models\Master\Item\ItemMaterial;
use App\Models\Master\Material\RawMaterial;
use App\Models\Production\GoodReceipt;
use Carbon\Carbon;

class ShippingInstructionController extends Controller
{
  private $route = 'sales/shipping-instruction';
  private $routeView = 'sales.shipping-instruction';
  private $params = [];

  public function __construct (Builder $datatablesBuilder)
  {
    $this->model = new ShippingInstruction();
    $this->datatablesBuilder = $datatablesBuilder;

    $this->params['route'] = $this->route;
    $this->params['routeView'] = $this->routeView;
    $this->params['shippingMethods'] = [
      $this->model::METHOD_IS_PICKUP => ['label' => 'pickup', 'label-color' => 'green'],
      $this->model::METHOD_IS_PICKUP_POINT => ['label' => 'pickup pada pickup point', 'label-color' => 'yellow'],
      $this->model::METHOD_IS_DELIVERY => ['label' => 'kirim', 'label-color' => 'red'],
    ];
  }

  public function search(Request $request)
  {
    $where = "status = " . $this->model::RELEASE;
    $response = [];

    if ($request->searchKey) {
      $where .= " and number like '%{$request->searchKey}%'";
    }

    try {
      $results = $this->model
      ->whereHas('shipping_instruction_details', function (qBuilder $query) {
        $query->whereRaw('(shipping_instruction_details.quantity - (
          select ifnull(sum(delivery_note_details.quantity), 0)
          from delivery_note_details
          where delivery_note_details.shipping_instruction_detail_id = shipping_instruction_details.id
          and delivery_note_details.deleted_at is null
          )) > 0');
        })
        ->whereRaw($where)
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
        'shipping_instruction_details', 'shipping_instruction_details.purchase_detail',
        'log_print' => function ($query) {
          $query->with(['employee']);
        }
        ])->first();

        foreach ($result->shipping_instruction_details as $shippingDetail) {

          $shippingDetail->item_material_id = $shippingDetail->purchase_detail->item_material_id;
          $shippingDetail->item_name = $shippingDetail->purchase_detail->item_name;
          $shippingDetail->quantity_max = $shippingDetail->quantity;
          $shippingDetail->total_price = $shippingDetail->purchase_detail->amount;
          $shippingDetail->price = $shippingDetail->purchase_detail->estimation_price;
          $shippingDetail->is_quantity_over = false;

          unset(
            $shippingDetail['status'],
            $shippingDetail['created_at'],
            $shippingDetail['updated_at'],
            $shippingDetail['deleted_at'],
            $shippingDetail['shipping_id'],
            $shippingDetail['purchase_detail']
          );
        }

        // $result->branch_id = $result->sales->warehouse_id;
        // $result->customer_id = $result->sales->customer_id;
        $result->name = $result->number;
        return response()->json($result, 200);
      }

      /**
      * Display a listing of the resource.
      *
      * @return \Illuminate\Http\Response
      */
      public function index(Request $request)
      {
        $releaseStatus = [
          $this->model::NOT_RELEASE => ['label' => 'not release', 'label-color' => 'yellow'],
          $this->model::RELEASE => ['label' => 'release', 'label-color' => 'blue'],
        ];

        $pickupMethods = $this->params['shippingMethods'];

        if ($request->ajax()) {
          // dd($this->model::with(['sales', 'sales.customer'])->get());
          $shippingInstructions = $this->model::with(['purchase'])
          ->whereHas('shipping_instruction_details');

          return Datatables::of($shippingInstructions)
          
          ->editColumn('status', function (ShippingInstruction $shipping) use ($releaseStatus) {
            return '<small class="label bg-'. $releaseStatus[$shipping->status]['label-color'] . '">' . $releaseStatus[$shipping->status]['label'] . '</small>';
          })
          ->editColumn('date', function (ShippingInstruction $shipping) {
            return '<a class="has-ajax-form text-red" href=""
            data-toggle="modal"
            data-target="#ajax-form"
            data-form-url="' . url($this->route) . '"
            data-load="'. url($this->route . '/' . $shipping->id . '/ajax-form') . '">
            ' . $shipping->date->format('m/d/Y') . ' - ' . $shipping->id . '
            </a>';
          })
          ->addColumn('action', function (ShippingInstruction $shipping) {
            $statusDropdown = '
            <select class="form-control status-select" data-id="' . $shipping->id . '">
                <option value="' . $this->model::NOT_RELEASE . '"' . ($shipping->received == $this->model::NOT_RELEASE ? ' selected' : '') . '>Not Received</option>
                <option value="' . $this->model::RELEASE . '"' . ($shipping->received == $this->model::RELEASE ? ' selected' : '') . '>Received</option>
            </select>';
        
            return \TransAction::table($this->route, $shipping, null, $shipping->log_print) . $statusDropdown;
          })
          ->rawColumns(['date', 'status', 'action'])
          ->make(true);
        }

        $this->params['model'] = $this->model;
        $this->params['datatable'] = $this->datatablesBuilder
        ->addColumn([ 'data' => 'date', 'name' => 'date', 'title' => 'Date-No' ])
        ->addColumn([ 'data' => 'number', 'name' => 'number', 'title' => 'Shipping Instruction No' ])
        ->addColumn([ 'data' => 'status', 'name' => 'status', 'title' => 'Release Status' ])
        ->addColumn([ 'data' => 'action', 'name' => 'action', 'title' => 'Action' ])
        ->parameters([
          'initComplete' => 'function() {
            $.getScript("'. asset("js/utomodeck.js") .'");
            $.getScript("'. asset("js/sales/shipping-index.js") .'");
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
        $this->params['model']['number'] = \RunningNumber::generate('shipping_instructions', 'number', \Config::get('transactions.shipping_instruction.code'));

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
          $shippingDetails = $request->shipping_instruction_details;

          $params = $request->all();
          $params['date'] = date('Y-m-d', strtotime($request->date));
          $params['status'] = $this->model::NOT_RELEASE;

          unset(
            $params['submit'],
            $params['_token'],
            $params['order_details'],
            $params['shipping_instruction_details']
          );

          $sales = Purchase::find($params['purchase_receipt_id']);

          // dd($params);

          $shipping = $this->model::create($params);
          
          if(!empty($shippingDetails) && count($shippingDetails) > 0) {
            foreach ($shippingDetails as $key => $shippingDetail) {
              unset($shippingDetail['id']);

              $shippingDetail['quantity'] = str_replace(',', '', $shippingDetail['quantity']);

              // dd($shippingDetail);
              $shipping->shipping_instruction_details()->create($shippingDetail);
            }
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
      * Display the specified resource.
      *
      * @param  int  $id
      * @return \Illuminate\Http\Response
      */
      public function show(Request $request, $id)
      {
        //
        $releaseStatus = [
          $this->model::NOT_RELEASE => ['label' => 'not release', 'label-color' => 'yellow'],
          $this->model::RELEASE => ['label' => 'release', 'label-color' => 'blue'],
        ];

        $pickupMethods = $this->params['shippingMethods'];

        if ($request->ajax()) {
          $shippingInstructions = $this->model::with(['sales', 'sales.customer'])
          ->whereHas('shipping_instruction_details');

          // return dd($shippingInstructions->get());
          if ($request->filled("periode_awal") && $request->filled("periode_akhir")) {
            $shippingInstructions = $shippingInstructions->whereBetween("date", [Carbon::parse($request->periode_awal)->startOfDay(), Carbon::parse($request->periode_akhir)->endOfDay()]);
          }

          if ($request->filled("filter_ship_no")) {
            $shippingInstructions = $shippingInstructions->where("number", "like", "%$request->filter_ship_no%");
          }

          if ($request->filled("filter_so_no")) {
            $shippingInstructions = $shippingInstructions->whereHas("sales", function($q) use($request) {
              $q->where("order_number", "like", "%$request->filter_so_no%");
            });
          }

          if ($request->filled("filter_customer")) {
            $shippingInstructions = $shippingInstructions->whereHas("sales.customer", function($q) use($request) {
              $q->where("name", "like", "%$request->filter_customer%");
            });
          }

          if ($request->filled("filter_status")) {
            $shippingInstructions = $shippingInstructions->where("status", $request->filter_status);
          }

          if ($request->filled("filter_method")) {
            $shippingInstructions = $shippingInstructions->where("shipping_method_id", $request->filter_method);
          }

          return Datatables::of($shippingInstructions)
          ->editColumn('total_price', function (ShippingInstruction $shipping) {
            return \Rupiah::format($shipping->sales->total_price);
          })
          ->editColumn('status', function (ShippingInstruction $shipping) use ($releaseStatus) {
            return '<small class="label bg-'. $releaseStatus[$shipping->status]['label-color'] . '">' . $releaseStatus[$shipping->status]['label'] . '</small>';
          })
          ->editColumn('shipping_method_id', function (ShippingInstruction $shipping) use ($pickupMethods) {
            return '<small class="label bg-'. $pickupMethods[$shipping->shipping_method_id]['label-color'] . '">'
            . $pickupMethods[$shipping->shipping_method_id]['label'] .
            '</small>';
          })
          ->editColumn('date', function (ShippingInstruction $shipping) {
            return '<a class="has-ajax-form text-red" href=""
            data-toggle="modal"
            data-target="#ajax-form"
            data-form-url="' . url($this->route) . '"
            data-load="'. url($this->route . '/' . $shipping->id . '/ajax-form') . '">
            ' . $shipping->date->format('m/d/Y') . ' - ' . $shipping->id . '
            </a>';
          })
          ->addColumn('order_number', function (ShippingInstruction $shipping) {
            return '<a class="text-red"
            target="_blank"
            href="'. url('/sales/order/' . $shipping->sales_id . '/edit') . '">
            ' . $shipping->sales->order_number . '
            </a>';
          })
          ->addColumn('action', function (ShippingInstruction $shipping) {
            return \TransAction::table($this->route, $shipping, null, $shipping->log_print);
          })
          ->rawColumns(['date', 'status', 'order_number', 'shipping_method_id', 'action'])
          ->make(true);
        }

        $this->params['model'] = $this->model;
        $this->params['datatable'] = $this->datatablesBuilder
        ->addColumn([ 'data' => 'date', 'name' => 'date', 'title' => 'Date-No' ])
        ->addColumn([ 'data' => 'number', 'name' => 'number', 'title' => 'Shipping Instruction No' ])
        ->addColumn([ 'data' => 'order_number', 'name' => 'order_number', 'title' => 'Sales Order No' ])
        ->addColumn([ 'data' => 'sales.customer.name', 'name' => 'sales.customer.name', 'title' => 'Customer' ])
        ->addColumn([ 'data' => 'total_price', 'name' => 'total_price', 'title' => 'Total Amount' ])
        ->addColumn([ 'data' => 'status', 'name' => 'status', 'title' => 'Release Status' ])
        ->addColumn([ 'data' => 'shipping_method_id', 'name' => 'shipping_method_id', 'title' => 'Shipping Method' ])
        ->addColumn([ 'data' => 'action', 'name' => 'action', 'title' => 'Action' ])
        ->parameters([
          'initComplete' => 'function() {
            $.getScript("'. asset("js/utomodeck.js") .'");
            $.getScript("'. asset("js/sales/shipping-index.js") .'");
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

              $currentSalesDetail = $shipping->shipping_instruction_details()->where('id', $id)->first();

              if(!empty($currentSalesDetail)) {
                $currentSalesDetail->update($shippingDetail);
                $keepShippingDetails[] = $currentSalesDetail->id;
                continue;
              }

              $newSalesDetail = $shipping->shipping_instruction_details()->create($shippingDetail);
              $keepShippingDetails[] = $newSalesDetail->id;
            }

            // hapus yang gk ada di request
            $shipping->shipping_instruction_details()->whereNotIn('id', $keepShippingDetails)->delete();
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
      public function destroy($id)
      {
        try {
          DB::beginTransaction();
          $shipping = $this->model->find($id);
          $shipping->shipping_instruction_details()->forceDelete();
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
          $shipping = $this->model->where('id', $id)->with([
            'shipping_instruction_details', 'purchase', 'shipping_instruction_details.purchase_detail',
            'branch'])->first();
          $sales = $shipping->sales;
          $params['model'] = $shipping;

          // if(!empty($shipping->log_print)) {
          //   if($isSuperAdmin) return \PrintFile::original($this->routeView . '.pdf', $params, 'Shipping-Instruction-' . $shipping->number);
          //   //print with watermark
          //   return \PrintFile::copy($this->routeView . '.pdf', $params, 'shipping-Instruction-' . $shipping->number);
          // }

          // //print without watermark
          // LogPrint::create([
          //   'transaction_code' => \Config::get('transactions.shipping_instruction.code'),
          //   'transaction_number' => $shipping->number,
          //   'employee_id' => Auth()->user()->id,
          //   'date' => now()
          // ]);


          // $shipping->shipping_instruction_details()->update(['status' => $this->model::RELEASE]);
          $shipping->status = $this->model::RELEASE;
          $shipping->save();

          DB::commit();
          // dd($params);
          // return json_encode($params);
          return \PrintFile::original($this->routeView . '.pdf', $params, 'Pengiriman Barang-' . $shipping->number);
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
          'branch_id' => ['required'],
          // 'created_by' => ['required'],
          'number' => ['required', 'unique:shipping_instructions,number' . $ignoredId],
          'date' => ['required'],
          'shipping_instruction_details.*.purchase_detail_id' => ['required'],
          'shipping_instruction_details.*.quantity' => ['required']
        ]);
      }

      public function updateStatus(Request $request, $id)
      {
          $this->validate($request, [
              'received' => 'required|in:' . implode(',', [
                  $this->model::NOT_RELEASE,
                  $this->model::RELEASE,
              ]),
          ]);

          try {
              DB::beginTransaction();

              $shippingInstruction = $this->model->findOrFail($id);
              $shippingInstruction->received = $request->input('received');
              $shippingInstruction->save();

              DB::commit();

              return response()->json([
                  'status' => 'success',
                  'message' => 'Status updated successfully.'
              ], 200);
          } catch (\Throwable $th) {
              DB::rollback();
              return response()->json([
                  'status' => 'error',
                  'message' => 'Failed to update status: ' . $th->getMessage()
              ], 500);
          }
}

    }
