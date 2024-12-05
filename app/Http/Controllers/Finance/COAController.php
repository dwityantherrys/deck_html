<?php

namespace App\Http\Controllers\Finance;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Models\Finance\COA;
use App\Jobs\ImportJob;

class COAController extends Controller
{
  public function __construct() {
    $this->model = new COA();
  }
  /**
  * Display a listing of the resource.
  *
  * @return \Illuminate\Http\Response
  */
  public function index()
  {
    //
    return view("finance.coa.index");
  }

  /**
  * Show the form for creating a new resource.
  *
  * @return \Illuminate\Http\Response
  */
  public function create()
  {
    //
    $lk_kategori = collect($this->model->whereNotNull("lk_kategori")->get()->pluck("lk_kategori"))->unique();
    $lk_pos = collect($this->model->whereNotNull("lk_pos")->get()->pluck("lk_pos"))->unique();
    return view("finance.coa.create")->with([
      "lk_kategori" => $lk_kategori,
      "lk_pos" => $lk_pos
    ]);
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
    $request->validate([
      "kode_akun" => ["required"],
      "nama_akun" => ["required"],
      "pos" => ["required"],
      "saldo" => ["required"],
    ]);
    COA::create($request->all());
    return redirect()->route("coa.index")->withSuccess("Berhasil menambah COA");
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
    $coa = COA::find($id);
    return response()->json($coa);
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
    $coa = COA::find($id);
    $lk_kategori = collect($this->model->whereNotNull("lk_kategori")->get()->pluck("lk_kategori"))->unique();
    $lk_pos = collect($this->model->whereNotNull("lk_pos")->get()->pluck("lk_pos"))->unique();
    return view("finance.coa.edit")->with([
      "coa" => $coa,
      "lk_kategori" => $lk_kategori,
      "lk_pos" => $lk_pos
    ]);
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
    $request->validate([
      "kode_akun" => ["required"],
      "nama_akun" => ["required"],
      "pos" => ["required"],
      "saldo" => ["required"],
    ]);

    COA::find($id)->update($request->all());
    return redirect()->back()->withSuccess("Berhasil menyimpan COA");
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
    COA::find($id)->delete();
    return redirect()->back()->withSuccess("Berhasil menghapus COA");
  }

  public function listCOA(Request $request) {
    $coa = COA::all();
    return datatables()->of($coa)
    ->editColumn("kode_akun_parent", function($coa_single) {
      return $coa_single->parent();
    })
    ->editColumn("pos", function($coa_single) {
      if ($coa_single->pos == 1) {
        return "Debet";
      }
      elseif ($coa_single->pos == 2) {
        return "Kredit";
      }
      else {
        return null;
      }
    })
    ->addColumn("action", function($coa_single) {
      return "<a href='". route("coa.edit", $coa_single->id) ."' class='btn btn-info'><i class='fa fa-pencil-square'></i></a> <button class='btn btn-danger btn-delete' data-id='". $coa_single->id ."'><i class='fa fa-trash'></i></button>";
    })
    ->make(true);
  }

  public function import(Request $request) {
    if ($request->hasFile('importfile')) {
        //UPLOAD FILE
        $file = $request->file('importfile');
        $filename = time() . '.' . $file->getClientOriginalExtension();
        $file->storeAs(
            'public', $filename
        );

        ImportJob::dispatch($filename);
        return redirect()->back()->withSuccess('Upload success');
    }
    return redirect()->back()->withError('Please choose file before');
  }

  public function getCOAAjax(Request $request) {
    if ($request->filled("search")) {
      $response = COA::where("kode_akun", "like", "%$request->search%")->orWhere("nama_akun", "like", "%$request->search%")->get();
    }
    else {
      $response = null;
    }
    return response()->json($response, 200);
  }
}
