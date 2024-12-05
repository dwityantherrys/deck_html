<?php

namespace App\Http\Controllers\Inventory;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\DB;

use App\Models\Inventory\Inventory;
use App\Models\Inventory\InventoryWarehouse;
use App\Models\Master\Material\RawMaterial;


class InventoryController extends Controller
{ 
    private $params = [];
    private $inventoryType = [];

    public function __construct ()
    {
      $this->model = new InventoryWarehouse();
    }

    // di pakai di good issued
    public function search(Request $request, $warehouseId, $inventoryType)
    {
      $where = '1=1';
      $response = [];

      if ($request->searchKey) {
        $where .= " and inventory_warehouse_number like '%{$request->searchKey}%'";
      }

      try {
        $results = $this->model->whereHas('inventory', function ($query) use ($inventoryType){
                        $query->where([ 'type_inventory' => $inventoryType ]);
                   })
                   ->where('warehouse_id', $warehouseId)
                   ->whereRaw($where)
                   ->get()
                   ->makeHidden(['created_at', 'updated_at']);

        foreach ($results as $key => $result) {
            $result->name = '(' . $result->inventory_warehouse_number . ')';

            $inventory = $result->inventory;
            if($inventory->type_movement == $inventory::TYPE_INVENTORY_RAW) {
                $rawMaterial = $inventory->raw_material;
                $result->name .= ' ' . $rawMaterial->name . ' ' . $rawMaterial->material->name . ' ' . $rawMaterial->thick . ' ' . $rawMaterial->color->name;
                continue;
            }

            $itemMaterial = $inventory->item_material;
            $result->name .= ' ' . $itemMaterial->item->name . ' ' . $itemMaterial->material->name . ' ' . $itemMaterial->thick . ' ' . $itemMaterial->color->name; 
        }         

        $response['results'] = $results;
      } catch (\Exception $e) {
        return response(['message' => $e->getMessage()], 500);
      }

      return response()->json($response, 200);
    }

    public function searchById ($warehouseId, $inventoryType, $id)
    {
        $result = $this->model->where('id', $id)->first();
        
        $result->name = $result->inventory_warehouse_number;
        return response()->json($result, 200);
    }

}