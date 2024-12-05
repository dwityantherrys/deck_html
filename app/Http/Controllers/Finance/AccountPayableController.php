<?php

namespace App\Http\Controllers\Finance;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;
use Yajra\DataTables\DataTables;
use Yajra\DataTables\Html\Builder;

use App\Models\LogPrint;
use App\Models\Sales\Sales;
use App\Models\Purchase\PurchaseInvoice;
use App\Models\Finance\AccountPayable;
use App\Models\Finance\COA;
use App\Models\Finance\FinanceJournal;


class AccountPayableController extends Controller
{
  private $route = 'finance/account-payable';
  private $routeView = 'finance.account-payable';
  private $params = [];
  private $apTypes = [];

  public function __construct (Builder $datatablesBuilder)
  {
    $this->model = new AccountPayable();
    $this->journal = new FinanceJournal();
    $this->no_urut = $this->journal->latest()->pluck("no_transaksi")->first() + 1;
    $this->datatablesBuilder = $datatablesBuilder;
    $this->arTypes = [
      $this->model::TYPE_DP => ['label' => 'downpayment', 'label-color' => 'yellow'],
      $this->model::TYPE_BILL => ['label' => 'total bill', 'label-color' => 'red']
    ];

    $this->params['route'] = $this->route;
    $this->params['routeView'] = $this->routeView;
  }

  /**
  * Display a listing of the resource.
  *
  * @return \Illuminate\Http\Response
  */
  public function index(Request $request)
  {
    $apTypes = $this->arTypes;
    if ($request->ajax()) {
      return Datatables::of($this->model::with(['purchase']))
      ->editColumn('type', function (AccountPayable $ap) use ($apTypes) {
        return '<small class="label bg-'. $apTypes[$ap->type]['label-color'] . '">' . $apTypes[$ap->type]['label'] . '</small>';
      })
      ->editColumn('amount', function (AccountPayable $ap) {
        return \Rupiah::format($ap->amount);
      })
      ->editColumn('balance', function (AccountPayable $ap) {
        return \Rupiah::format($ap->amount);
      })
      ->editColumn('created_at', function (AccountPayable $ap) {
        return $ap->created_at->format('m/d/Y');
      })
      ->editColumn('updated_at', function (AccountPayable $ap) {
        return $ap->updated_at->format('m/d/Y');
      })
      ->addColumn('purchase_order_number', function (AccountPayable $ap) {
        return '<a class="text-red"
        target="_blank"
        href="'. url('/purchase/order/' . $ap->purchase->id . '/edit') . '">
        ' . $ap->purchase->order_number . '
        </a>';
      })
      ->addColumn('purchase_invoice', function (AccountPayable $ap) {
        return $ap->purchase->purchase_receipt->purchase_invoice->number;
      })
      ->addColumn('action', function (AccountPayable $ap) {
        if($ap->balance === 0) return '<span class="text-green" style="font-weight: 600">Terbayar</span>';

        return '<div class="btn-group">
        <button
        class="payable-paid btn btn-default text-green"
        data-target="' . url($this->route . '/' . $ap->id) . '"
        data-token="' . csrf_token() . '">
        <i class="fa fa-check-circle-o"></i>
        </button>
        </div>';
      })
      ->rawColumns(['purchase_order_number', 'type', 'action'])
      ->make(true);
    }

    $this->params['model'] = $this->model;
    $this->params['datatable'] = $this->datatablesBuilder
    ->addColumn([ 'data' => 'purchase_order_number', 'name' => 'purchase_order_number', 'title' => 'PO. No' ])
    ->addColumn([ 'data' => 'purchase_invoice', 'name' => 'purchase_invoice', 'title' => 'PI. No' ])
    ->addColumn([ 'data' => 'type', 'name' => 'type', 'title' => 'Type Trans' ])
    ->addColumn([ 'data' => 'amount', 'name' => 'amount', 'title' => 'Amount' ])
    ->addColumn([ 'data' => 'balance', 'name' => 'balance', 'title' => 'Balance' ])
    ->addColumn([ 'data' => 'created_at', 'name' => 'created_at', 'title' => 'Created at' ])
    ->addColumn([ 'data' => 'updated_at', 'name' => 'updated_at', 'title' => 'Update at' ])
    ->addColumn([ 'data' => 'action', 'name' => 'action', 'title' => 'Action' ])
    ->parameters([
      'initComplete' => 'function() {
        $.getScript("'. asset("js/utomodeck.js") .'");
      }',
    ]);
    $this->params['kode_akun'] = COA::where("nama_akun", "like", "%kas%")->orWhere("nama_akun", "like", "%bank%")->get();
    return view($this->routeView . '.index', $this->params);
  }

  /**
  * Show the form for creating a new resource.
  * add adjustment inventory
  * @return \Illuminate\Http\Response
  */
  public function create()
  {
    $this->params['model'] = new InventoryWarehouse();

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

  }

  /**
  * Display the specified resource.
  *
  * @param  int  $id
  * @return \Illuminate\Http\Response
  */
  public function show(Request $request, $id)
  {

  }

  /**
  * Show the form for editing the specified resource.
  * untuk adjustment
  * @param  int  $id
  * @return \Illuminate\Http\Response
  */
  public function edit($id)
  {
    $this->params['model'] = InventoryWarehouse::find($id);
    return view($this->routeView . '.edit', $this->params);
  }

  /**
  * Update the specified resource in storage.
  * untuk adjustment
  * @param  \Illuminate\Http\Request  $request
  * @param  int  $id
  * @return \Illuminate\Http\Response
  */
  public function update(Request $request, $id)
  {
    try {
      DB::beginTransaction();

      $purchaseInvoice = PurchaseInvoice::where('number', $request->purchase_invoice_number)->first();
      if(empty($purchaseInvoice)) return response()->json(['message' => 'purchase invoice number not found'], 500);

      $accountPayable = $this->model::find($id);
      $ppn_masukkan = $accountPayable->purchase->total_price * 0.1;
      if ($accountPayable->type == 1) {
        $dp = $this->model::where("purchase_id", $purchaseInvoice->id)->where("type", 0)->first();
        if (isset($dp)) {
          $gl_debet = [
            [
              "no_transaksi" => $this->no_urut,
              "kode_akun" => "1301", // Persediaan
              "pos" => 1,
              "nominal" => $accountPayable->amount + $dp->amount,
              "created_at" => $this->journal->freshTimestamp(),
              "updated_at" => $this->journal->freshTimestamp(),
            ],
            [
              "no_transaksi" => $this->no_urut,
              "kode_akun" => "1402.6", // PPN Masukkan
              "pos" => 1,
              "nominal" => $ppn_masukkan,
              "created_at" => $this->journal->freshTimestamp(),
              "updated_at" => $this->journal->freshTimestamp(),
            ],
          ];
          $gl_kredit = [
            [
              "no_transaksi" => $this->no_urut,
              "kode_akun" => $request->kode_akun, // Kas atau Bank
              "pos" => 2,
              "nominal" => $accountPayable->amount + $ppn_masukkan,
              "created_at" => $this->journal->freshTimestamp(),
              "updated_at" => $this->journal->freshTimestamp(),
            ],
            [
              "no_transaksi" => $this->no_urut,
              "kode_akun" => "1401.1", // UM Pembelian
              "pos" => 2,
              "nominal" => $dp->amount,
              "created_at" => $this->journal->freshTimestamp(),
              "updated_at" => $this->journal->freshTimestamp(),
            ],
          ];
        }
        else {
          $gl_debet = [
            [
              "no_transaksi" => $this->no_urut,
              "kode_akun" => "1301", // Persediaan
              "pos" => 1,
              "nominal" => $accountPayable->amount,
              "created_at" => $this->journal->freshTimestamp(),
              "updated_at" => $this->journal->freshTimestamp(),
            ],
            [
              "no_transaksi" => $this->no_urut,
              "kode_akun" => "1402.6", // PPN Masukkan
              "pos" => 1,
              "nominal" => $ppn_masukkan,
              "created_at" => $this->journal->freshTimestamp(),
              "updated_at" => $this->journal->freshTimestamp(),
            ]
          ];
          $gl_kredit = [
            [
              "no_transaksi" => $this->no_urut,
              "kode_akun" => $request->kode_akun, // Kas atau Bank
              "pos" => 2,
              "nominal" => $accountPayable->amount + $ppn_masukkan,
              "created_at" => $this->journal->freshTimestamp(),
              "updated_at" => $this->journal->freshTimestamp(),
            ]
          ];
        }
        $purchaseInvoice->update([
            'status' => PurchaseInvoice::PAID_OFF,
            'balance' => 0,
            'paid_date' => date('Y-m-d H:i:s')
        ]);
      }
      elseif($accountPayable->type == 0) {
        $gl_debet = [
          [
            "no_transaksi" => $this->no_urut,
            "kode_akun" => "1401.1", // UANG MUKA PEMBELIAN RUPIAH
            "pos" => 1,
            "nominal" => $accountPayable->amount,
            "created_at" => $this->journal->freshTimestamp(),
            "updated_at" => $this->journal->freshTimestamp(),
          ]
        ];
        $gl_kredit = [
          [
            "no_transaksi" => $this->no_urut,
            "kode_akun" => $request->kode_akun, // kas atau Bank
            "pos" => 2,
            "nominal" => $accountPayable->amount,
            "created_at" => $this->journal->freshTimestamp(),
            "updated_at" => $this->journal->freshTimestamp(),
          ]
        ];
      }
      $this->journal->insert(array_merge($gl_debet, $gl_kredit));
      $accountPayable->balance = 0;
      $accountPayable->save();

      DB::commit();
      return response()->json([], 204);


    } catch (\Throwable $th) {
      DB::rollback();

      return response()->json(['message' => $th->getMessage()], 500);
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
}
