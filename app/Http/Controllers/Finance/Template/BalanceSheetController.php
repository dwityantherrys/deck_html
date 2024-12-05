<?php

namespace App\Http\Controllers\Finance\Template;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Models\Finance\TemplateBalanceSheet;
use DB;

class BalanceSheetController extends Controller
{

    public function __construct() {
      $this->template = new TemplateBalanceSheet();
    }
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        //
        return view("finance.template.balance-sheet.index");
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        //
        return view("finance.template.balance-sheet.create");
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
        DB::beginTransaction();
        try {
          $template = $this->template->create([
            "pos" => $request->pos,
            "posisi" => $request->posisi,
            "akun_penambah" => $request->filled("akun_penambah") ? implode(",", $request->akun_penambah) : null,
            "akun_pengurang" => $request->filled("akun_pengurang") ? implode(",", $request->akun_pengurang) : null,
          ]);
          DB::commit();
          return redirect()->route("finance.template.balance-sheet.index")->withSuccess("Berhasil menambah template: ". $template->pos);
        } catch (\Exception $e) {
          DB::rollback();
          return redirect()->back()->withError("Gagal menambah template: <b>". $e->getMessage() ."</b>")->withInput();
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
        return view("finance.template.balance-sheet.show")->with([
          "template" => $this->template->find($id),
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
        DB::beginTransaction();
        try {
          $template = $this->template->find($id);
          $template->update([
            "pos" => $request->pos,
            "posisi" => $request->posisi,
            "akun_penambah" => $request->filled("akun_penambah") ? implode(",", $request->akun_penambah) : null,
            "akun_pengurang" => $request->filled("akun_pengurang") ? implode(",", $request->akun_pengurang) : null,
          ]);
          DB::commit();
          return redirect()->route("finance.template.balance-sheet.index")->withSuccess("Berhasil memperbarui template: <b>". $template->pos ."</b>");
        } catch (\Exception $e) {
          DB::rollback();
          return redirect()->back()->withError("Gagal memperbarui template: <b>". $e->getMessage() ."</b>")->withInput();
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
        //
    }

    public function ajaxDataTable() {
      return datatables()->of($this->template->get())
      ->editColumn("posisi", function($template) {
        if ($template->posisi == 1) {
          return "Aset";
        }
        else {
          return "Liabilitas";
        }
      })
      ->addColumn("action", function($template) {
        return "<a href='". route("finance.template.balance-sheet.show", $template->id) ."' class='btn btn-primary'><i class='fa fa-pencil'></i> Edit</a>";
      })
      ->toJson();
    }
}
