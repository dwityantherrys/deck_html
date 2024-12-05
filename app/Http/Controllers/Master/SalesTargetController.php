<?php

namespace App\Http\Controllers\Master;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Models\Master\SalesTarget;
use Carbon\Carbon;

class SalesTargetController extends Controller
{

  public function __construct() {
    $this->view = "master.sales-target.";
    $this->route = "sales-target.";
    $this->model = new SalesTarget();
  }
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        //
        return view($this->view ."index");
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        //
        return view($this->view ."create")->with([
          "routeView" => $this->view
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
        $model = $this->model;
        $model->periode = Carbon::createFromFormat("m-Y", $request->periode)->startOfMonth();
        $model->target = $request->target;
        $model->save();
        return redirect()->route($this->route ."index")->withSuccess("Berhasil menambahkan sales target");
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
        return view($this->view ."show")->with([
          "routeView" => $this->view,
          "model" => $this->model->find($id)
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
        $model = $this->model->find($id);
        $model->periode = Carbon::createFromFormat("m-Y", $request->periode)->startOfMonth();
        $model->target = $request->target;
        $model->save();
        return redirect()->route($this->route ."index")->withSuccess("Berhasil menambahkan sales target");
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

    public function ajaxDataTable(Request $request)
    {
      return datatables()->of($this->model->get())
      ->editColumn("periode", function($target) {
        return Carbon::parse($target->periode)->format("F Y");
      })
      ->addColumn("action", function($target) {
        return "<a href='". route("sales-target.show", $target->id) ."' class='btn btn-primary'><i class='fa fa-pencil'></i> Edit</a>";
      })
      ->toJson();
    }
}
