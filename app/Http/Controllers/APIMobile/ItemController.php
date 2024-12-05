<?php

namespace App\Http\Controllers\APIMobile;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

use App\Models\Master\Warehouse;
use App\Models\Inventory\Inventory;
use App\Models\Master\Item\Item;
use App\Models\Master\Item\ItemMaterial;
use App\Models\Master\Item\ItemReview;

class ItemController extends Controller
{
    private $activeUser;

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request, $categoryId = null)
    {
      $this->activeUser = $request->user;

      try {
        $where = "1=1";
        
        $filters = [];
        // $filterMinPrice = null;
        // $filterMaxPrice = null;
        if(!empty($request->category_id)) $filters[] = "item_category_id = {$request->category_id}";
        if(!empty($request->item_name)) $filters[] = "lower(name) like lower('%{$request->item_name}%')";
        // if(!empty($request->minimum_price)) $filterMinPrice = $request->minimum_price;
        // if(!empty($request->maximum_price)) $filterMaxPrice = $request->maximum_price;
        if(count($filters) > 0) $where .= ' and (' . implode(' or ', $filters) . ')';

        if(!empty($categoryId)) $where .= " and item_category_id = {$categoryId}";

        $items = Item::orderBy('updated_at', 'DESC')
                  ->active()
                  ->whereRaw($where)
                  ->with([
                      'images',
                      'item_materials',
                      'item_materials.material',
                      'item_materials.color'
                    ])
                  ->paginate(10);

        foreach ($items as $key => $item) {
          //set default value if image empty
          if(count($item->images) <= 0) {
            $item->images[] = [
              "id" => 1,
              "image" => "/img/no-image.png",
              "item_id" => 1,
              "is_thumbnail" => 1,
              "is_active" => 1,
              "created_at" => date('Y-m-d H:i:s'),
              "updated_at" => date('Y-m-d H:i:s'),
              "image_url" => asset("/img/no-image.png")
            ];
          }

          foreach ($item->item_materials as $keyIM => $itemMaterial) {
            $itemMaterial->price = $this->_getPriceItem($itemMaterial->id);
          }
        }          

        return response()->json($items, 200)->setEncodingOptions(JSON_NUMERIC_CHECK);
      } catch (\Exception $e) {
        return response()->json(['message' => $e->getMessage()], 500);
      }
    }

    public function _getPriceItem ($itemId)
    {
      // get warehouse terdekat by region, jika district, pakai id citynya
      $regionType = $this->activeUser->region_type;
      $regionId = $this->activeUser->region_id;

      if($regionType === $this->activeUser::REGION_TYPE_DISTRICT){
        $regionType = $this->activeUser::REGION_TYPE_CITY;
        $regionId = $this->activeUser->region_district->city_id; 
      }

      $warehouse = Warehouse::where([
        'region_type' => $regionType,
        'region_id' => $regionId
      ])->first();

      if(empty($warehouse)) return 0;

      // get inventory by item
      $inventory = Inventory::where([
        'type_inventory' => Inventory::TYPE_INVENTORY_FINISH,
        'reference_id' => $itemId
      ])->first();

      if(empty($inventory)) return 0;

      /** 
       * item di inventory warehouse bisa lebih dari 1, 
       * karena tiap purchase receipt di catat menjadi inv warehouse baru
       * pakai harga paling terbaru 
       * */
      $selectedInventory = $inventory->inventory_warehouses()
        ->where('warehouse_id', $warehouse->id)
        ->where('selling_price', '>', 0)
        ->orderBy('created_at', 'DESC')
        ->first();

      if(empty($selectedInventory)) return 0;

      return $selectedInventory->selling_price;
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
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show(Request $request, $id)
    {
      $this->activeUser = $request->user;
      
      try {
        $item = Item::orderBy('updated_at', 'DESC')
        ->active()
        ->where('id', $id)
        ->with([
          'images',  
          'item_materials',
          'item_materials.material',
          'item_materials.color'
        ])
        ->first()
        ->makeHidden(['length', 'has_length_options']);

        foreach ($item->item_materials as $keyIM => $itemMaterial) {
          $itemMaterial->price = $this->_getPriceItem($itemMaterial->id);
        }

        return response()->json($item, 200)->setEncodingOptions(JSON_NUMERIC_CHECK);
      } catch (\Exception $e) {
        return response()->json(['message' => $e->getMessage()], 500);
      }
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
}
