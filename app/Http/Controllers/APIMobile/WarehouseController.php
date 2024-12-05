<?php

namespace App\Http\Controllers\APIMobile;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

use App\Models\Master\Warehouse;

class WarehouseController extends Controller
{
    public function __construct ()
    {
      $this->model = new Warehouse();  
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index($cityId = null)
    {
      $where = "1=1";

      if(!empty($cityId)) $where .= " and (region_type = 0 and region_id = {$cityId})";

      try {
        $warehouses = $this->model->makeHidden(['created_at', 'updated_at'])
                    ->whereRaw($where)
                    ->paginate(10);           

        return response()->json($warehouses, 200);
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
                ->find($id);

      try {
        return response()->json($city, 200);
      } catch (\Exception $e) {
        return response()->json(['message' => $e->getMessage()], 500);
      }
    }
}
