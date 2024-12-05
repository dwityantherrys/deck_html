<?php

namespace App\Http\Controllers\APIMobile;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

use App\Models\Master\BannerMobile;

class BannerMobileController extends Controller
{
    public function __construct ()
    {
        $this->model = new BannerMobile();
    }
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    { 
        try {
          $banners = $this->model->makeHidden(['created_at', 'updated_at'])
                      ->active()
                      ->get();
  
          return response()->json($banners, 200);
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
