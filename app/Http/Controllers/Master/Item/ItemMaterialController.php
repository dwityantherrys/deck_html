<?php

namespace App\Http\Controllers\Master\Item;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;

use App\Models\Inventory\Inventory;
use App\Models\Master\Warehouse;
use App\Models\Master\Item\ItemMaterial;

class ItemMaterialController extends Controller
{
    private $route = 'master/item/material';
    private $routeView = 'master.item/item/material';
    private $params = [];
    private $activeWarehouse;

    public function __construct ()
    {
      $this->model = new ItemMaterial();
      $this->params['route'] = $this->route;
      $this->params['routeView'] = $this->routeView;
    }

    /**
     * dipakai di menu purchase request
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
            $value->name = $value->item->name . ' ' . $value->material->name . ' ' . $value->thick . 'mm ' .$value->color->name;
        }

        $response['results'] = $results;
      } catch (\Exception $e) {
        return response(['message' => $e->getMessage()], 500);
      }

      return response()->json($response, 200);
    }

    public function searchInWarehouseById($id, $warehouseId)
    {
      $this->activeWarehouse = Warehouse::find($warehouseId);
      $result = $this->model->find($id);

      //get item price
      $result->name = $result->item->name . ' ' . $result->material->name . ' ' . $result->thick . 'mm ' .$result->color->name;
      $result->price = $this->_getPriceItem($result->id);

      return response()->json($result, 200);
    }

    public function searchById($id)
    {
      $result = $this->model->find($id);

      $result->name = $result->item->name . ' ' . $result->material->name . ' ' . $result->thick . 'mm ' .$result->color->name;

      return response()->json($result, 200);
    }

    public function _getPriceItem ($itemId)
    {
      // get warehouse terdekat by id
      $warehouse = $this->activeWarehouse;
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
      return $selectedInventory->selling_price;
    }
}
