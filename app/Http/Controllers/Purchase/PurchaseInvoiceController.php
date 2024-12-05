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
use App\Models\Purchase\PurchaseInvoice;
use App\Models\Master\Item\ItemMaterial;
use App\Models\Master\Material\RawMaterial;
use Carbon\Carbon;
use App\Models\Finance\AccountPayable;

class PurchaseInvoiceController extends Controller
{
    private $route = 'purchase/invoice';
    private $routeView = 'purchase.invoice';
    private $params = [];

    public function __construct (Builder $datatablesBuilder)
    {
      $this->model = new PurchaseInvoice();
      $this->datatablesBuilder = $datatablesBuilder;

      $this->params['route'] = $this->route;
      $this->params['routeView'] = $this->routeView;
      $this->params['prTypes'] = [
            PurchaseInvoice::TAX_NONE => ['label' => 'None', 'label-color' => 'green'],
            PurchaseInvoice::TAX_11 => ['label' => 'Pajak 11%', 'label-color' => 'green'],
            PurchaseInvoice::TAX_INCLUDE => ['label' => 'Pajak 11% Include', 'label-color' => 'green']

        ];
  
    }

    private function _formatReceiptDetails ($receiptDetail, $format = null)
    {
        $receiptAdjs = [];
        $finalReceiptAdjs = [];
        dd($receiptDetail);
 
        // foreach ($receiptDetails as $receiptDetail => $value) {
            // dd($value);
          // $receiptDetail->raw_material_id = $receiptDetail->purchase_detail->raw_material_id;
            $receiptDetail->item_name = $receiptDetail->purchase_details->item_name;

            unset(
                $receiptDetail['status'],
                $receiptDetail['purchase_order_id'],
                $receiptDetail['discount'],
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
                    $itemMaterial = $receiptDetail->purchase_detail->item_material;
                    $purchaseDetail['item_name'] = $itemMaterial->name . ' ' . $itemMaterial->material->name . ' ' . $itemMaterial->thick . 'mm ' .$itemMaterial->color->name;
                    // $purchaseDetail['item_name'] = $rawMaterial->name . ' ' . $rawMaterial->material->name . ' ' . $rawMaterial->thick . 'mm ' .$rawMaterial->color->name;
                }

                $purchaseDetail['purchase_detail_id'] = $receiptDetail['purchase_detail_id'];
                $purchaseDetail['quantity'] = $purchaseDetail['quantity']-$purchaseDetail['quantity_left'];
                $purchaseDetail['quantity_max'] = $purchaseDetail['quantity']-$purchaseDetail['quantity_left'];
                $purchaseDetail['discount'] = 0;
                $purchaseDetail['has_adjustment'] = $hasAdjustment;

                unset(
                    $purchaseDetail['request_status'],
                    $purchaseDetail['order_status'],
                    $purchaseDetail['purchase_id'],
                    $purchaseDetail['created_at'],
                    $purchaseDetail['updated_at'],
                    $purchaseDetail['deleted_at'],
                    $receiptDetail['status'],
                    $receiptDetail['discount'],
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
                
            }

            if($format == 'invoice-format') {
                // $rawMaterial = $receiptDetail->purchase_detail->raw_material;
                // $receiptDetail['item_name'] = $rawMaterial->name . ' ' . $rawMaterial->material->name . ' ' . $rawMaterial->thick . 'mm ' .$rawMaterial->color->name;

                $itemMaterial = $receiptDetail->purchase_detail->item_material;
                $receiptDetail['item_name'] = $itemMaterial->name . ' ' . $itemMaterial->material->name . ' ' . $itemMaterial->thick . 'mm ' .$itemMaterial->color->name;
            }
            $receiptAdjs[$receiptDetail->purchase_detail_id]['adjs'][] = $receiptDetail;
        // }

        foreach ($receiptAdjs as $key => $value) {
            $finalReceiptAdjs[] = $value;
        }

        return $finalReceiptAdjs;
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

    public function searchById ($id, $format = null)
    {
        $result = $this->model->where('id', $id)->with([
            'purchase_order','purchase_order.purchase_details',
            'log_print' => function ($query) {
                $query->with(['employee']);
            }
        ])->first();

        $receiptDetails = $result->purchase_order;

        $result->name = $result->number;
        // $result->invoice_details = $this->_formatReceiptDetails($receiptDetails, $format);
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
            $this->model::BILL_PENDING => ['label' => 'pending', 'label-color' => 'yellow'],
            $this->model::BILLED => ['label' => 'di tagihkan', 'label-color' => 'blue'],
            $this->model::PAID_OFF => ['label' => 'terbayar', 'label-color' => 'green'],
        ];

        if ($request->ajax()) {
            return Datatables::of($this->model::with(['purchase_order']))
                        ->editColumn('bill', function (PurchaseInvoice $purcInv) {
                            return \Rupiah::format($purcInv->bill);
                        })
                        ->editColumn('status', function (PurchaseInvoice $purcInv) use ($invoiceStatus) {
                            return '<small class="label bg-'. $invoiceStatus[$purcInv->status]['label-color'] . '">' . $invoiceStatus[$purcInv->status]['label'] . '</small>';
                        })
                        ->editColumn('date_of_issued', function (PurchaseInvoice $purcInv) {
                            return '<a class="has-ajax-form text-red" href=""
                                data-toggle="modal"
                                data-target="#ajax-form"
                                data-form-url="' . url($this->route) . '"
                                data-load="'. url($this->route . '/' . $purcInv->id . '/ajax-form') . '">
                                ' . $purcInv->date_of_issued->format('m/d/Y') . ' - ' . $purcInv->id . '
                                </a>';
                        })
                        ->editColumn('due_date', function (PurchaseInvoice $purcInv) {
                            return $purcInv->due_date->format('m/d/Y');
                        })
                        ->editColumn('paid_date', function (PurchaseInvoice $purcInv) {
                            return '<a class="has-ajax-form text-red" href=""
                                data-toggle="modal"
                                data-target="#ajax-form"
                                data-form-url="' . url($this->route) . '"
                                data-load="'. url($this->route . '/' . $purcInv->id . '/ajax-form') . '">
                                ' . optional($purcInv->paid_date)->format('m/d/Y') . ' - ' . $purcInv->id . '
                                </a>';
                        })
                        ->addColumn('order_number', function (PurchaseInvoice $purcInv) {
                            return '<a class="text-red"
                                    target="_blank"
                                    href="'. url('/purchase/order/' . $purcInv->purchase_order_id . '/edit') . '">
                                    ' . $purcInv->purchase_order->id . '
                                </a>';
                        })
                        ->addColumn('action', function (PurchaseInvoice $purcInv) {
                            return \TransAction::table($this->route, $purcInv, null, $purcInv->log_print);
                        })
                        ->rawColumns(['order_number', 'date_of_issued', 'paid_date', 'status', 'action'])
                        ->make(true);
        }

        $this->params['model'] = $this->model;
        $this->params['datatable'] = $this->datatablesBuilder
                                        ->addColumn([ 'data' => 'date_of_issued', 'name' => 'date_of_issued', 'title' => 'Date-No' ])
                                        ->addColumn([ 'data' => 'order_number', 'name' => 'order_number', 'title' => 'Purch. Order No' ])
                                        ->addColumn([ 'data' => 'due_date', 'name' => 'due_date', 'title' => 'Due Date Inv.' ])
                                        ->addColumn([ 'data' => 'bill', 'name' => 'bill', 'title' => 'Amount' ])
                                        ->addColumn([ 'data' => 'status', 'name' => 'status', 'title' => 'Inv. Status' ])
                                        ->addColumn([ 'data' => 'action', 'name' => 'action', 'title' => 'Action' ])
                                        ->parameters([
                                            'initComplete' => 'function() {
                                                $.getScript("'. asset("js/utomodeck.js") .'");
                                                $.getScript("'. asset("js/purchase/invoice-index.js") .'");
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
        $this->params['model']->purchase_details = [];
        $this->params['model']['number'] = \RunningNumber::generate('purchase_invoices', 'number', \Config::get('transactions.purchase_invoice.code'));
        
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
            // dd($request->all());
            // $salesDetails = $request->sales_details;

            $params = $request->all();
            $params['purchase_order_id'] = $params['purchase_order_number'];
            $params['date_of_issued'] = date('Y-m-d', strtotime($request->date_of_issued));
            $params['due_date'] = date('Y-m-d', strtotime($request->due_date));
            $params['status'] = $this->model::BILL_PENDING;
            $params['balance'] = $request->bill;

            unset(
                $params['purchase_order_number'],
                $params['vendor_id'],
                $params['submit'],
                $params['_token'],
                $params['invoice_details']
            );

            $invoice = $this->model::create($params);

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
                $redirectOnSuccess .= "?print=" .$invoice->id;
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
        $invoiceStatus = [
          $this->model::BILL_PENDING => ['label' => 'pending', 'label-color' => 'yellow'],
          $this->model::BILLED => ['label' => 'di tagihkan', 'label-color' => 'blue'],
          $this->model::PAID_OFF => ['label' => 'terbayar', 'label-color' => 'green'],
        ];

        if ($request->ajax()) {
          $query = $this->model::with(['purchase_order','purchase_order.purchase_details', 'purchase_order.vendor','purchase_order.pic', 'purchase_order.vendor.profile',
                'purchase_order.vendor.profile.default_address','purchase_order.vendor.profile.default_address.region_city', 'purchase_order.vendor.profile.default_address.region_district']);
          if ($request->filled("periode_awal") && $request->filled("periode_akhir")) {
            $query = $query->whereBetween("date_of_issued", [Carbon::parse($request->periode_awal)->startOfDay(), Carbon::parse($request->periode_akhir)->endOfDay()]);
          }
          if($request->filled("filter_pr_no")){
            // $query = $query->whereHas("purchase_receive", function($q) use($request) {
            //   $q->where("number", "like", "%$request->filter_pr_no%");
            // });
          }
          if($request->filled("filter_due_date")){
            $query = $query->whereDate("due_date", $request->filter_due_date);
          }
          if($request->filled("filter_status")){
            $query = $query->where("status", $request->filter_status);
          }
          return Datatables::of($query)
          ->editColumn('bill', function (PurchaseInvoice $purcInv) {
            return \Rupiah::format($purcInv->bill);
          })
          ->editColumn('status', function (PurchaseInvoice $purcInv) use ($invoiceStatus) {
            return '<small class="label bg-'. $invoiceStatus[$purcInv->status]['label-color'] . '">' . $invoiceStatus[$purcInv->status]['label'] . '</small>';
          })
          ->editColumn('date_of_issued', function (PurchaseInvoice $purcInv) {
            return '<a class="has-ajax-form text-red" href=""
            data-toggle="modal"
            data-target="#ajax-form"
            data-form-url="' . url($this->route) . '"
            data-load="'. url($this->route . '/' . $purcInv->id . '/ajax-form') . '">f
            ' . $purcInv->date_of_issued->format('m/d/Y') . ' - ' . $purcInv->id . '
            </a>';
          })
          ->editColumn('due_date', function (PurchaseInvoice $purcInv) {
            return $purcInv->due_date->format('m/d/Y');
          })
          ->editColumn('paid_date', function (PurchaseInvoice $purcInv) {
            return '<a class="has-ajax-form text-red" href=""
            data-toggle="modal"
            data-target="#ajax-form"
            data-form-url="' . url($this->route) . '"
            data-load="'. url($this->route . '/' . $purcInv->id . '/ajax-form') . '">
            ' . optional($purcInv->paid_date)->format('m/d/Y') . ' - ' . $purcInv->id . '
            </a>';
          })
          ->addColumn('receive_number', function (PurchaseInvoice $purcInv) {
            return '<a class="text-red"
            target="_blank"
            href="'. url('/purchase/receipt/' . $purcInv->purchase_receive_id . '/edit') . '">
            ' . $purcInv->purchase_receive->number . '
            </a>';
          })
          ->addColumn('action', function (PurchaseInvoice $purcInv) {
            return \TransAction::table($this->route, $purcInv, null, $purcInv->log_print);
          })
          ->rawColumns(['receive_number', 'date_of_issued', 'paid_date', 'status', 'action'])
          ->make(true);
        }

        $this->params['model'] = $this->model;
        $this->params['datatable'] = $this->datatablesBuilder
        ->addColumn([ 'data' => 'date_of_issued', 'name' => 'date_of_issued', 'title' => 'Date-No' ])
        ->addColumn([ 'data' => 'receive_number', 'name' => 'receive_number', 'title' => 'Purch. Receive No' ])
        ->addColumn([ 'data' => 'due_date', 'name' => 'due_date', 'title' => 'Due Date Inv.' ])
        ->addColumn([ 'data' => 'bill', 'name' => 'bill', 'title' => 'Amount' ])
        ->addColumn([ 'data' => 'status', 'name' => 'status', 'title' => 'Inv. Status' ])
        ->addColumn([ 'data' => 'action', 'name' => 'action', 'title' => 'Action' ])
        ->parameters([
          'initComplete' => 'function() {
            $.getScript("'. asset("js/utomodeck.js") .'");
            $.getScript("'. asset("js/purchase/invoice-index.js") .'");
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
            // $salesDetails = $request->sales_details;

            $params = $request->all();
            $params['purchase_order_id'] = $params['purchase_order_number'];
            $params['date_of_issued'] = date('Y-m-d', strtotime($request->date_of_issued));
            $params['due_date'] = date('Y-m-d', strtotime($request->due_date));
            $params['status'] = $this->model::BILL_PENDING;
            $params['balance'] = $request->bill;

            unset(
                $params['purchase_order_number'],
                $params['vendor_id'],
                $params['submit'],
                $params['_token'],
                $params['invoice_details']
            );

            $invoice = $this->model->where('id', $id)->first();
            $invoice->update($params);

            // if(!empty($salesDetails) && count($salesDetails) > 0) {
            //     foreach ($salesDetails as $key => $salesDetail) {
            //         $id = $salesDetail['id'];
            //         $itemMaterial = ItemMaterial::find($salesDetail['item_material_id']);
            //         $item = $itemMaterial->item;

            //         unset($params['length_selected']);

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

            //         $currentSalesDetail = $sales->sales_details()->where('id', $id)->first();

            //         if(!empty($currentSalesDetail)) {
            //             $currentSalesDetail->update($salesDetail);
            //             $keepSalesDetails[] = $currentSalesDetail->id;
            //             continue;
            //         }

            //         $newSalesDetail = $sales->sales_details()->create($salesDetail);
            //         $keepSalesDetails[] = $newSalesDetail->id;
            //     }

            //     // hapus yang gk ada di request
            //     $sales->sales_details()->whereNotIn('id', $keepSalesDetails)->delete();
            // }

            if($submitAction == 'save_print') {
                $redirectOnSuccess .= "?print=" .$invoice->id;
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
        $roleUser = request()->user()->role->name;
        $isSuperAdmin = $roleUser === 'super_admin';

        try {
            DB::beginTransaction();
            $invoice = $this->model->where('id', $id)->with([
                'purchase_order','purchase_order.purchase_details', 'purchase_order.vendor','purchase_order.pic', 'purchase_order.vendor.profile',
                'purchase_order.vendor.profile.default_address','purchase_order.vendor.profile.default_address.region_city', 'purchase_order.vendor.profile.default_address.region_district'])->first();
            $params['model'] = $invoice;

            // if(!empty($invoice->log_print)) {
            //     if($isSuperAdmin) return \PrintFile::original($this->routeView . '.pdf', $params, 'Purchase-Invoice-' . $invoice->number);

            //     //print with watermark
            //     return \PrintFile::copy($this->routeView . '.pdf', $params, 'Purchase-Invoice-' . $invoice->number);
            // }

            //print without watermark
            LogPrint::create([
                'transaction_code' => \Config::get('transactions.purchase_invoice.code'),
                'transaction_number' => $invoice->number,
                'employee_id' => Auth()->user()->id,
                'date' => now()
            ]);

            $this->model->where('id', $id)->update(['status' => $this->model::BILLED]);

            //check jika menggunakan dp, create ar untuk dp
            // if($invoice->downpayment > 0) {
            //     $arDP = AccountPayable::create([
            //         'purchase_id' => $invoice->purchase_receive->purchase_id,
            //         'amount' => $invoice->downpayment,
            //         'balance' => $invoice->downpayment,
            //         'note' => '',
            //         'type' => AccountPayable::TYPE_DP
            //     ]);
            // }

            //create ap untuk sisa dari dp jika pakai dp atau ar keseluruhan.
            // $apBalance = $invoice->bill - $invoice->downpayment;

            // $apBill = AccountPayable::create([
            //     'purchase_id' => $invoice->purchase_receive->purchase_id,
            //     'amount' => $apBalance,
            //     'balance' => $apBalance,
            //     'note' => '',
            //     'type' => AccountPayable::TYPE_BILL
            // ]);

            DB::commit();
            // dd($params);
            // return view($this->routeView . '.pdf', $params);


            return \PrintFile::original($this->routeView . '.pdf', $params, 'Purchase-Invoice-' . $invoice->number);
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
            'number' => ['required', 'unique:purchase_invoices,number' . $ignoredId],
            'date_of_issued' => ['required'],
            'due_date' => ['required']
        ]);
    }
}
