<?php

namespace App\Http\Controllers\APIMobile;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

use App\Models\Master\City\City;

class CityController extends Controller
{
    public function __construct ()
    {
      $this->model = new City();  
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index($provinceId = null)
    {
      $where = "1=1";

      if(!empty($provinceId)) $where .= " and province_id = {$provinceId}";

      try {
        $cities = $this->model->makeHidden(['created_at', 'updated_at'])
                    ->with(['province:id,name'])
                    ->whereRaw($where)
                    ->get();           

        return response()->json($cities, 200);
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
      $city = $this->model->makeHidden(['created_at', 'updated_at'])
                ->with(['province:id,name'])
                ->find($id);

      try {
        return response()->json($city, 200);
      } catch (\Exception $e) {
        return response()->json(['message' => $e->getMessage()], 500);
      }
    }
}
