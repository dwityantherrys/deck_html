<?php

namespace App\Http\Controllers\Finance;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Models\Finance\FinanceJournal;
use App\Models\Finance\COA;
use Carbon\Carbon;
use App\Models\Finance\TemplateIncomeStatement;

class IncomeStatementController extends Controller
{

  public function __construct(Request $request)
  {
    $this->journal = new FinanceJournal();
    if (isset($request->periode)) {
      $this->periode = Carbon::createFromFormat("d-m-Y", $request->periode);
    }
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
    return view("finance.income-statement.index");
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
    foreach ($this->coa->where("lk", "labarugi")->get() as $key => $value) {
      if ($value->pos == 2) {
        $result[$value->lk_kategori][$value->lk_pos][$value->nama_akun]["tambah"] = [
          "nama_akun" => $value->nama_akun,
          "total" => $this->journal->whereBetween("created_at", [$this->periode_awal, $this->periode_akhir])->where("kode_akun", $value->kode_akun)->get()->sum("nominal"),
        ];
      }
      if ($value->pos == 1) {
        $result[$value->lk_kategori][$value->lk_pos][$value->nama_akun]["kurang"] = [
          "nama_akun" => $value->nama_akun,
          "total" => $this->journal->whereBetween("created_at", [$this->periode_awal, $this->periode_akhir])->where("kode_akun", $value->kode_akun)->get()->sum("nominal"),
        ];
      }
    }
    // return dd($result);
    return view("finance.income-statement.show")->with([
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

  public function ajaxSelect(Request $request)
  {
    if ($request->filled("search")) {
      $coa = $this->coa->where("nama_akun", "like", "%$request->search%")->orWhere("kode_akun", "like", "%$request->search%")->get();
    }
    else {
      $coa = $this->coa->get();
    }
    return response()->json($coa);
  }
}
