<?php

namespace App\Http\Controllers\APIMobile;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

use App\Models\Master\City\Province;
use App\Models\Master\City\City;

class ProvinceController extends Controller
{
    public function __construct ()
    {
      $this->model = new Province();
    }
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
      try {
        $provinces = $this->model->select('id', 'name')->get();
        return response()->json($provinces, 200);
      } catch (\Exception $e) {
        return response()->json(['message' => $e->getMessage()], 500);
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
      $province = $this->model->select('id', 'name')->find($id);

      try {
        return response()->json($province, 200);
      } catch (\Exception $e) {
        return response()->json(['message' => $e->getMessage()], 500);
      }
    }
}
