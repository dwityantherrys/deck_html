<?php

namespace App\Http\Controllers\Master\Item;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;

use App\Models\Master\Item\Item;
use App\Models\Master\Item\ItemNew;
use App\Models\Master\Item\ItemImage;

class ItemNewController extends Controller
{
    private $route = 'master/item/item';
    private $routeView = 'master.item.item';
    private $routeUpload = 'img/item';
    private $params = [];

    public function __construct ()
    {
      $this->model = new ItemNew();
      $this->params['route'] = $this->route;
      $this->params['routeView'] = $this->routeView;
    }
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
     
     public function search(Request $request)
    {
      $where = "1=1";
      $response = [];

      if ($request->searchKey) {
        $where .= " and name like '%{$request->searchKey}%'";
      }

      try {
        $results = $this->model::whereRaw($where)
                   ->get()
                   ->makeHidden(['created_at', 'updated_at']);

        foreach ($results as $key => $value) {
            $value->name = $value->name;
        }

        $response['results'] = $results;
      } catch (\Exception $e) {
        return response(['message' => $e->getMessage()], 500);
      }

      return response()->json($response, 200);
    }
     
    public function index()
    {
      $this->params['items'] = $this->model->get();
      return view($this->routeView . '.index', $this->params);
    }

    public function searchById($name)
    {
      $result = $this->model->where('id', $name)->first();

      return response()->json($result, 200);
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
        // dd($request->all());
        $storedImages = [];
        $validator = $this->_validate($request->all());

        if($validator->fails())
        {
            return redirect()
                ->back()
                ->withErrors($validator)
                ->withInput();
        }

        try {
            
            DB::beginTransaction();
            $params = $request->all();

            $params['max_custom_length'] = empty($params['max_custom_length']) ? 0 : $params['max_custom_length'];
            empty($params['has_length_options']) ? $params['has_length_options'] = 0 : $params['length'] = 0;
            unset($params['_token']);

            $item = $this->model::create($params);


            DB::commit();

            $request->session()->flash('notif', [
                'code' => 'success',
                'message' => str_replace(".", " ", $this->routeView) . ' success ' . __FUNCTION__ . 'd',
            ]);

            return redirect($this->route);

        } catch (\Throwable $th) {
            echo $th->getMessage();
            DB::rollback();

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
        $keepItemMaterials = [];
        $validator = $this->_validate($request->all());

        if($validator->fails())
        {
            dd($validator->errors());
            return redirect()
            ->back()
            ->withErrors($validator)
            ->withInput();
        }

        try {
            DB::beginTransaction();

            $params = $request->all();
            $itemMaterials = $request->item_materials;
            $images = $request->images;

            $params['max_custom_length'] = empty($params['max_custom_length']) ? 0 : $params['max_custom_length'];
            $params['charge_custom_length'] = empty($params['charge_custom_length']) ? 0 : $params['charge_custom_length'];
            empty($params['has_length_options']) ? $params['has_length_options'] = 0 : $params['length'] = 0;
            unset($params['_token'], $params['_method'], $params['id'], $params['item_materials'], $params['images']);

            $item = $this->model->where('id', $id)->first();
            $item->update($params);

            if(!empty($itemMaterials) && count($itemMaterials) > 0) {
                foreach ($itemMaterials as $key => $itemMaterial) {
                    $id = $itemMaterial['id'];

                    // bersihin paramater request, biar langsung save (gk perlu mass assigment)
                    unset($itemMaterial['material']);
                    unset($itemMaterial['id']);

                    $currentItemMaterial = $item->item_materials()->where('id', $id)->first();

                    if(!empty($currentItemMaterial)){
                        $currentItemMaterial->update($itemMaterial);
                        $keepItemMaterials[] = $currentItemMaterial->id;
                        continue;
                    }

                    $newItemMaterial = $item->item_materials()->create($itemMaterial);
                    $keepItemMaterials[] = $newItemMaterial->id;
                }

                // hapus yang gk ada di request
                $item->item_materials()->whereNotIn('id', $keepItemMaterials)->delete();
            }

            if(!empty($images) && count($images) > 0) {
                foreach ($images as $key => $image) {
                    $imagePath = NULL;

                    if ($image['file']) {
                        $imagePath = $image['file']->store($this->routeUpload . '/' .$item->id, 'public');
                        $storedImages[] = $imagePath;
                    }

                    unset($image['file']);

                    if(!empty($image['is_thumbnail'])) {
                        ItemImage::where('item_id', $item->id)->update(['is_thumbnail' => 0]);
                    }

                    $item->images()->create([
                        'image' => $imagePath,
                        'is_thumbnail' => !empty($image['is_thumbnail']) ? $image['is_thumbnail'] : 0,
                        'is_active' => !empty($image['is_active']) ? $image['is_active'] : 0,
                    ]);
                }
            }

            DB::commit();

            $request->session()->flash('notif', [
                'code' => 'success',
                'message' => str_replace(".", " ", $this->routeView) . ' success ' . __FUNCTION__ . 'd',
            ]);

            return redirect($this->route);

        } catch (\Throwable $th) {
            DB::rollback();

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
            DB::beginTransaction();
            $item = $this->model->find($id);
            $item->item_materials()->delete();
            $item->images()->delete();
            $item->delete();

            \Storage::disk('public')->deleteDirectory($this->routeUpload . '/' . $id);

            DB::commit();
            return response()->json([], 204);
        } catch (\Throwable $th) {
            DB::rollback();
            return response()->json(['message' => $th->getMessage()], 500);
        }
    }

    public function updateImage(Request $request, $id)
    {
        try {
            DB::beginTransaction();
            $image = ItemImage::find($id);

            if($request->is_thumbnail == 'true') {
                ItemImage::where('item_id', $image->item_id)->update(['is_thumbnail' => 0]);
            }

            $image->is_thumbnail = $request->is_thumbnail == 'true' ? 1 : 0;
            $image->is_active = $request->is_active == 'true' ? 1 : 0;
            $image->save();

            DB::commit();
            return response()->json([], 200);
        } catch (\Throwable $th) {
            DB::rollback();
            return response()->json(['message' => $th->getMessage()], 500);
        }
    }

    public function destroyImage($id)
    {
        try {
            DB::beginTransaction();
            $image = ItemImage::find($id);
            $imagePath = $image->image;

            $image->delete();

            \Storage::disk('public')->delete($imagePath);

            DB::commit();
            return response()->json([], 204);
        } catch (\Throwable $th) {
            DB::rollback();
            return response()->json(['message' => $th->getMessage()], 500);
        }
    }

    private function _validate ($request)
    {
        return Validator::make($request, [
            'name' => ['required'],
            'price' => ['required'],
        ]);
    }
}
