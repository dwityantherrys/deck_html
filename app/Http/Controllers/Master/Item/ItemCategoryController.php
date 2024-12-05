<?php

namespace App\Http\Controllers\Master\Item;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Validator;

use App\Models\Master\Item\ItemCategory;

class ItemCategoryController extends Controller
{
    private $route = 'master/item/category';
    private $routeView = 'master.item.category';
    private $routeUpload = 'img/item-category';
    private $params = [];

    public function __construct ()
    {
      $this->model = new ItemCategory();
      $this->params['route'] = $this->route;
      $this->params['routeView'] = $this->routeView;
    }

    public function search(Request $request)
    {
      $where = "1=1";
      $response = [];

      if ($request->searchKey) {
        $where .= " and name like '%{$request->searchKey}%'";
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
      $this->params['assetCategories'] = $this->model->get();
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
                'name' => $request->name,
                'description' => $request->description,
                'image' => $image,
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
            $itemCategory = $this->model::where('id', $id)->first();
            $image = $itemCategory->image;

            if ($request->hasFile('image')) {
                if ($image) {
                    \Storage::disk('public')->delete($image);
                }

                $image = $request->file('image')->store($this->routeUpload, 'public');
            }

            unset($request['_token'], $request['_method'], $request['id']);
            $itemCategory->update([
                'name' => $request->name,
                'description' => $request->description,
                'image' => $image,
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
            $itemCategory = $this->model->find($id);

            if ($itemCategory->image) {
                \Storage::disk('public')->delete($itemCategory->image);
            }

            $itemCategory->delete();
            return response()->json([], 204);
        } catch (\Throwable $th) {
            return response()->json(['message' => $th->getMessage()], 500);
        }
    }

    private function _validate ($request)
    {
        return Validator::make($request, [
            'name' => ['required'],
            'image' => 'nullable|image|max:2048'
        ]);
    }
}
