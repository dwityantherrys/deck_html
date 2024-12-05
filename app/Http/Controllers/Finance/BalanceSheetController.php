<?php

namespace App\Http\Controllers\Finance;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Models\Finance\FinanceJournal;
use App\Models\Finance\COA;
use App\Models\Finance\TemplateBalanceSheet;
use Carbon\Carbon;

class BalanceSheetController extends Controller
{
  public function __construct(Request $request) {
    $this->view = "finance.balance-statement.";
    $this->journal = new FinanceJournal();
    if (isset($request->periode_awal)) {
      $this->periode_awal = Carbon::parse($request->periode_awal)->startOfDay();
    }
    if (isset($request->periode_akhir)) {
      $this->periode_akhir = Carbon::parse($request->periode_akhir)->endOfDay();
    }
    $this->coa = new COA();
  }
  /**
  * Display a listing of the resource.
  *
  * @return \Illuminate\Http\Response
  */
  public function index()
  {
    //
    return view($this->view. "index");
  }

  /**
  * Show the form for creating a new resource.
  *
  * @return \Illuminate\Http\Response
  */
  public function create()
  {
    //
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
    $result = [];
    foreach ($this->coa->where("lk", "neraca")->get() as $key => $value) {
      if ($value->pos == "1") {
        $result[$value->lk_kategori][$value->lk_pos][$value->nama_akun]["tambah"] = [
          "nama_akun" => $value->nama_akun,
          "total" => $this->journal->whereBetween("created_at", [$this->periode_awal, $this->periode_akhir])->where("kode_akun", $value->kode_akun)->where("pos", 1)->get()->sum("nominal") - $this->journal->whereBetween("created_at", [$this->periode_awal, $this->periode_akhir])->where("kode_akun", $value->kode_akun)->where("pos", 2)->get()->sum("nominal"),
        ];
      }
      if ($value->pos == "2") {
        $result[$value->lk_kategori][$value->lk_pos][$value->nama_akun]["kurang"] = [
          "nama_akun" => $value->nama_akun,
          "total" => $this->journal->whereBetween("created_at", [$this->periode_awal, $this->periode_akhir])->where("kode_akun", $value->kode_akun)->where("pos", 2)->get()->sum("nominal") - $this->journal->whereBetween("created_at", [$this->periode_awal, $this->periode_akhir])->where("kode_akun", $value->kode_akun)->where("pos", 1)->get()->sum("nominal"),
        ];
      }
      // foreach ($value->coa_penambah() as $coa) {
      //   $result[$value->posisi][$value->pos]["tambah"][] = [
      //     "nama_akun" => $coa->nama_akun,
      //     "total" => $this->journal->whereBetween("created_at", [$this->periode_awal, $this->periode_akhir])->where("kode_akun", $coa->kode_akun)->get()->sum("nominal"),
      //   ];
      // }
      // foreach ($value->coa_pengurang() as $coa) {
      //   $result[$value->posisi][$value->pos]["kurang"][] = [
      //     "nama_akun" => $coa->nama_akun,
      //     "total" => $this->journal->whereBetween("created_at", [$this->periode_awal, $this->periode_akhir])->where("kode_akun", $coa->kode_akun)->get()->sum("nominal"),
      //   ];
      // }
    }
    // return dd($result);
    return view($this->view. "show")->with([
      "data" => $result,
    ]);
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
}
