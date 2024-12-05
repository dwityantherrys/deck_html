<?php

namespace App\Http\Controllers\Inventory\Asset;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;

use App\Models\Inventory\Asset\Asset;

class AssetController extends Controller
{
    private $route = 'inventory/asset/asset';
    private $routeView = 'inventory.asset.asset';
    private $routeUpload = 'img/asset';
    private $params = [];

    public function __construct ()
    {
      $this->model = new Asset();
      $this->params['route'] = $this->route;
      $this->params['routeView'] = $this->routeView;
    }

    public function search(Request $request)
    {
      $where = "1=1";
      $response = [];

      if ($request->searchKey) {
        $where .= " andname like '%{$request->searchKey}%'";
      }

      try {
        $results = $this->model->whereRaw($where)
                   ->get()
                   ->makeHidden(['created_at', 'updated_at']);

        $response['results'] = $results;
      } catch (\Exception $e) {
        return response(['message' => $e->getMessage()], 500);
      }

      return response()->json($response, 200);
    }

    public function searchById($id)
    {
      return response()->json($this->model->find($id), 200);
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
      $this->params['assets'] = $this->model->get();
      return view($this->routeView . '.index', $this->params);
    }
    
    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        $this->params['model'] = $this->model;
        return view($this->routeView . '.create', $this->params);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $validator = $this->_validate($request->all());

        if($validator->fails())
        {
            return redirect()
                ->back()
                ->withErrors($validator)
                ->withInput();
        }

        try {
            $image = NULL;
            if ($request->hasFile('image')) {
                $image = $request->file('image')->store($this->routeUpload, 'public');
            }

            $this->model::create([
                'code' => $request->code,
                'name' => $request->name,
                'description' => $request->description,
                'image' => $image,
                'brand_id' => $request->brand_id,
                'asset_category_id' => $request->asset_category_id,
                'is_active' => $request->is_active
            ]);
            
            $request->session()->flash('notif', [
                'code' => 'success',
                'message' => str_replace(".", " ", $this->routeView) . ' success ' . __FUNCTION__ . 'd',
            ]);
            return redirect($this->route);

        } catch (\Throwable $th) {
            if ($request->hasFile('image')) {
                \Storage::disk('public')->delete($image);
            }
            
            $request->session()->flash('notif', [
                'code' => 'failed ' . __FUNCTION__ . 'd',
                'message' => str_replace(".", " ", $this->routeView) . ' : ' . $th->getMessage(),
            ]);

            return redirect()
                ->back()
                ->withInput();
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
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        $this->params['model'] = $this->model->find($id);
        return view($this->routeView . '.edit', $this->params);
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
        $validator = $this->_validate($request->all());

        if($validator->fails())
        {
            return redirect()
                ->back()
                ->withErrors($validator)
                ->withInput();
        }

        try {
            $assetCategory = $this->model::where('id', $id)->first();
            $image = $assetCategory->image;

            if ($request->hasFile('image')) {
                if ($image) {
                    \Storage::disk('public')->delete($image);
                }

                $image = $request->file('image')->store($this->routeUpload, 'public');
            }

            unset($request['_token'], $request['_method'], $request['id']);
            $itemCategory->update([
                'code' => $request->code,
                'name' => $request->name,
                'description' => $request->description,
                'image' => $image,
                'asset_category_id' => $request->asset_category_id,
                'brand_id' => $request->brand_id,
                'is_active' => $request->is_active           
            ]);
            
            $request->session()->flash('notif', [
                'code' => 'success',
                'message' => str_replace(".", " ", $this->routeView) . ' success ' . __FUNCTION__ . 'd',
            ]);
            return redirect($this->route);

        } catch (\Throwable $th) {
            if ($request->hasFile('image')) {
                \Storage::disk('public')->delete($image);
            }

            $request->session()->flash('notif', [
                'code' => 'failed ' . __FUNCTION__ . 'd',
                'message' => str_replace(".", " ", $this->routeView) . ' : ' . $th->getMessage(),
            ]);

            return redirect()
                ->back()
                ->withInput();
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
        try {
            $assetCategory = $this->model->find($id);

            if ($assetCategory->image) {
                \Storage::disk('public')->delete($assetCategory->image);
            }

            $assetCategory->delete();
            return response()->json([], 204);
        } catch (\Throwable $th) {
            return response()->json(['message' => $th->getMessage()], 500);
        }
    }

    private function _validate ($request)
    {
        return Validator::make($request, [
            'name' => ['required'],
            'code' => ['required'],
            'image' => 'nullable|image|max:2048'
        ]);
    }
}
