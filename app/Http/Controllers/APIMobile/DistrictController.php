<?php

namespace App\Http\Controllers\APIMobile;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

use App\Models\Master\City\District;

class DistrictController extends Controller
{
    public function __construct ()
    {
      $this->model = new District();  
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index($cityId = null)
    {
      if(empty($cityId)) return response()->json(['message' => 'city_id tidak boleh kosong'], 400);

      try {
        $districts = $this->model->makeHidden(['created_at', 'updated_at'])
                    ->with(['city:id,name,type,postal_code'])
                    ->where('city_id', $cityId)
                    ->get();

        return response()->json($districts, 200);
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
      $district = $this->model->makeHidden(['created_at', 'updated_at'])
                ->with(['city:id,name,type,postal_code'])
                ->find($id);

      try {
        return response()->json($district, 200);
      } catch (\Exception $e) {
        return response()->json(['message' => $e->getMessage()], 500);
      }
    }
}
