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
use App\Models\Finance\AccountReceivable;
use App\Models\Finance\COA;
use App\Models\Finance\FinanceJournal;


class AccountReceivableController extends Controller
{
  private $route = 'finance/account-receivable';
  private $routeView = 'finance.account-receivable';
  private $params = [];
  private $arTypes = [];

  public function __construct (Builder $datatablesBuilder)
  {
    $this->model = new AccountReceivable();
    $this->gl = new FinanceJournal();
    $this->no_urut = $this->gl->latest()->pluck("no_transaksi")->first() + 1;
    $this->datatablesBuilder = $datatablesBuilder;
    $this->arTypes = [
      $this->model::TYPE_DP => ['label' => 'downpayment', 'label-color' => 'yellow'],
      $this->model::TYPE_BILL => ['label' => 'total bill', 'label-color' => 'red']
    ];

    $this->params['route'] = $this->route;
    $this->params['routeView'] = $this->routeView;
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
      $results = $this->model->whereRaw($where)
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

    /**
    * Display a listing of the resource.
    *
    * @return \Illuminate\Http\Response
    */
    public function index(Request $request)
    {
      $arTypes = $this->arTypes;

      if ($request->ajax()) {
        return Datatables::of($this->model::with(['sales', 'sales_transactions'])->whereHas("sales"))
        ->editColumn('type', function (AccountReceivable $ar) use ($arTypes) {
          return '<small class="label bg-'. $arTypes[$ar->type]['label-color'] . '">' . $arTypes[$ar->type]['label'] . '</small>';
        })
        ->editColumn('amount', function (AccountReceivable $ar) {
          return \Rupiah::format($ar->amount);
        })
        ->editColumn('balance', function (AccountReceivable $ar) {
          return \Rupiah::format($ar->balance);
        })
        ->editColumn('created_at', function (AccountReceivable $ar) {
          return $ar->created_at->format('m/d/Y');
        })
        ->editColumn('updated_at', function (AccountReceivable $ar) {
          return $ar->updated_at->format('m/d/Y');
        })
        ->addColumn('sales_order_number', function (AccountReceivable $ar) {
          return '<a class="text-red"
          target="_blank"
          href="'. url('/sales/order/' . $ar->sales->id . '/edit') . '">
          ' . $ar->sales->order_number . '
          </a>';
        })
        ->addColumn('action', function (AccountReceivable $ar) {
          if($ar->balance === 0) return '<span class="text-green" style="font-weight: 600">Terbayar</span>';

          if($ar->sales->transaction_channel === Sales::TRANSACTION_CHANNEL_WEB) {
            // return '<div class="btn-group">
            // <button
            // class="confirmation-paid btn btn-default text-green"
            // data-target="' . url($this->route . '/' . $ar->id) . '"
            // data-token="' . csrf_token() . '">
            // <i class="fa fa-check-circle-o"></i>
            // </button>
            // </div>';
            return '<div class="btn-group">
            <button
            class="btn-pay btn btn-default text-green"
            data-target="' . url($this->route . '/' . $ar->id) . '"
            data-token="' . csrf_token() . '">
            <i class="fa fa-check-circle-o"></i>
            </button>
            </div>';
          }
        })
        ->rawColumns(['sales_order_number', 'type', 'action'])
        ->make(true);
      }

      $this->params['model'] = $this->model;
      $this->params['datatable'] = $this->datatablesBuilder
      ->addColumn([ 'data' => 'sales_order_number', 'name' => 'sales_order_number', 'title' => 'SO. No' ])
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
      $result = $this->model->where('id', $id)->with([
        'sales',
        'sales.payment_bank_channel'
        // 'request_log_print' => function ($query) {
        //   $query->with(['employee']);
        // }
        ])->first();

        $result->name = $result->request_number;
        return response()->json($result, 200);
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

        $accountReceivable = $this->model::find($id);
        $ppn_keluaran = $accountReceivable->sales->grand_total_price * 0.1;

        if ($accountReceivable->type == 1) {
          $dp = $this->model::where("sales_id", $accountReceivable->sales_id)->where("type", 0)->first();
          if (isset($dp)) {
            $gl_debet = [
              [
                "no_transaksi" => $this->no_urut,
                "kode_akun" => "2601", // UM Penjualan
                "pos" => 1,
                "nominal" => $dp->amount,
                "created_at" => $this->gl->freshTimestamp(),
                "updated_at" => $this->gl->freshTimestamp(),
              ],
              [
                "no_transaksi" => $this->no_urut,
                "kode_akun" => $request->kode_akun, // Kas atau Bank
                "pos" => 1,
                "nominal" => $accountReceivable->amount + $ppn_keluaran,
                "created_at" => $this->gl->freshTimestamp(),
                "updated_at" => $this->gl->freshTimestamp(),
              ]
            ];
            $gl_kredit = [
              [
                "no_transaksi" => $this->no_urut,
                "kode_akun" => "4101", // Penjualan
                "pos" => 2,
                "nominal" => $accountReceivable->amount + $dp->amount,
                "created_at" => $this->gl->freshTimestamp(),
                "updated_at" => $this->gl->freshTimestamp(),
              ],
              [
                "no_transaksi" => $this->no_urut,
                "kode_akun" => "2506", // PPN Keluaran
                "pos" => 2,
                "nominal" => $ppn_keluaran,
                "created_at" => $this->gl->freshTimestamp(),
                "updated_at" => $this->gl->freshTimestamp(),
              ]
            ];
          }
          else {
            $gl_debet = [
              [
                "no_transaksi" => $this->no_urut,
                "kode_akun" => $request->kode_akun, // Kas atau Bank
                "pos" => 1,
                "nominal" => $accountReceivable->amount + $ppn_keluaran,
                "created_at" => $this->gl->freshTimestamp(),
                "updated_at" => $this->gl->freshTimestamp(),
              ]
            ];
            $gl_kredit = [
              [
                "no_transaksi" => $this->no_urut,
                "kode_akun" => "4101", // Penjualan
                "pos" => 2,
                "nominal" => $accountReceivable->amount,
                "created_at" => $this->gl->freshTimestamp(),
                "updated_at" => $this->gl->freshTimestamp(),
              ],
              [
                "no_transaksi" => $this->no_urut,
                "kode_akun" => "2506", // PPN Keluaran
                "pos" => 2,
                "nominal" => $ppn_keluaran,
                "created_at" => $this->gl->freshTimestamp(),
                "updated_at" => $this->gl->freshTimestamp(),
              ]
            ];
          }
        }
        elseif($accountReceivable->type == 0) {
          $gl_debet = [
            [
              "no_transaksi" => $this->no_urut,
              "kode_akun" => $request->kode_akun, // kas atau Bank
              "pos" => 1,
              "nominal" => $accountReceivable->amount,
              "created_at" => $this->gl->freshTimestamp(),
              "updated_at" => $this->gl->freshTimestamp(),
            ]
          ];
          $gl_kredit = [
            [
              "no_transaksi" => $this->no_urut,
              "kode_akun" => "2601", // um
              "pos" => 2,
              "nominal" => $accountReceivable->amount,
              "created_at" => $this->gl->freshTimestamp(),
              "updated_at" => $this->gl->freshTimestamp(),
            ]
          ];
        }

        $this->gl->insert(array_merge($gl_debet, $gl_kredit));

        $accountReceivable->balance = 0;
        $accountReceivable->save();

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
