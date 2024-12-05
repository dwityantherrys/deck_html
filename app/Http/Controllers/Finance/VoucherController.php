<?php

namespace App\Http\Controllers\Finance;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Models\Finance\Voucher;
use App\Models\Finance\COA;
use App\Models\Finance\FinanceJournal;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class VoucherController extends Controller
{

  public function __construct() {
    $this->model = new Voucher();
    $this->coa = new COA();
    $this->gl = new FinanceJournal();
    $this->no_urut = $this->gl->latest()->pluck("no_transaksi")->first() + 1;
  }
  /**
  * Display a listing of the resource.
  *
  * @return \Illuminate\Http\Response
  */
  public function index()
  {
    //
    return view("finance.voucher.index");
  }

  /**
  * Show the form for creating a new resource.
  *
  * @return \Illuminate\Http\Response
  */
  public function create()
  {
    //
    return view("finance.voucher.create");
  }

  /**
  * Store a newly created resource in storage.
  *
  * @param  \Illuminate\Http\Request  $request
  * @return \Illuminate\Http\Response
  */
  public function store(Request $request)
  {
    //
    try {
      DB::beginTransaction();
      $this->model->create([
        "journal_no_urut" => $this->no_urut,
        "keterangan" => $request->keterangan,
        "nama_pic" => $request->nama_pic,
        "tanggal_transaksi" => Carbon::createFromFormat("Y-m-d", $request->tanggal_transaksi),
      ]);

      $gl_debet = [
        [
          "no_transaksi" => $this->no_urut,
          "kode_akun" => $request->kode_biaya,
          "pos" => 1,
          "nominal" => $request->nominal_biaya,
          "created_at" => $this->gl->freshTimestamp(),
          "updated_at" => $this->gl->freshTimestamp(),
        ],
      ];
      $gl_kredit = [
        [
          "no_transaksi" => $this->no_urut,
          "kode_akun" => $request->sumber_pembiayaan,
          "pos" => 2,
          "nominal" => $request->nominal_biaya,
          "created_at" => $this->gl->freshTimestamp(),
          "updated_at" => $this->gl->freshTimestamp(),
        ],
      ];
      $this->gl->insert(array_merge($gl_debet, $gl_kredit));
      DB::commit();
      return redirect()->route("voucher.index")->withSuccess("Berhasil menyimpan voucher");
    } catch (Throwable $e) {
      DB::rollback();
      report($e);
      return redirect()->back()->withError("Terdapat kesalahan saat menyimpan voucher")->withInput();
    }

  }

  /**
  * Display the specified resource.
  *
  * @param  int  $id
  * @return \Illuminate\Http\Response
  */
  public function show($id)
  {
    //
    $model = $this->model->with("journal.coa")->find($id);
    return response()->json($model);
  }

  /**
  * Show the form for editing the specified resource.
  *
  * @param  int  $id
  * @return \Illuminate\Http\Response
  */
  public function edit($id)
  {
    //
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
    //
  }

  /**
  * Remove the specified resource from storage.
  *
  * @param  int  $id
  * @return \Illuminate\Http\Response
  */
  public function destroy($id)
  {
    //
  }

  public function voucherTable()
  {
    return datatables()->of($this->model->get())
    ->addColumn("action", function($voucher) {
      return "<button type='button' class='btn btn-primary btn-modal' data-id='". $voucher->id ."'><i class='fa fa-eye'></i></button>";
    })
    ->toJson();
  }

  public function ajaxVoucher(Request $request)
  {
    if ($request->type == "biaya") {
      if ($request->filled("search")) {
        $coa = $this->coa->where("nama_akun", "like", "%$request->search%")->where(function($q) {
          $q->orWhere("nama_akun", "like", "%biaya%")
          ->orWhere("nama_akun", "like", "%beban%");
        })->get();
      }
      else {
        $coa = $this->coa->where("nama_akun", "like", "%biaya%")->orWhere("nama_akun", "like", "%beban%")->get();
      }
    }
    elseif ($request->type == "sumber_pembiayaan") {
      if ($request->filled("search")) {
        $coa = $this->coa->where("nama_akun", "like", "%$request->search%")->where(function($q) {
          $q->where("nama_akun", "like", "%bank%")
          ->orWhere("nama_akun", "like", "%kas%");
        })->get();
      }
      else {
        $coa = $this->coa->where("nama_akun", "like", "%kas%")->orWhere("nama_akun", "like", "%bank%")->get();
      }
    }
    return response()->json($coa);
  }
}
