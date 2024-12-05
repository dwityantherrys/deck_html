<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Sales\Sales;
use App\Models\Sales\SalesInvoice;
use App\Models\Sales\SalesDetail;
use App\Models\Master\SalesTarget;
use App\Models\Inventory\Inventory;
use App\Models\Master\Warehouse;
use App\Models\Purchase\Purchase;

use Carbon\Carbon;

class HomeController extends Controller
{
  /**
  * Create a new controller instance.
  *
  * @return void
  */
  public function __construct()
  {
    $this->sales = new Sales();
    $this->salesinvoice = new SalesInvoice();
    $this->salestarget = new SalesTarget();
    $this->salesdetail = new SalesDetail();
    $this->inventory = new Inventory();
    $this->warehouse = new Warehouse();
    $this->purchase = new Purchase();
    $this->middleware('auth');
  }

  /**
  * Show the application dashboard.
  *
  * @return \Illuminate\Contracts\Support\Renderable
  */
  public function index()
  {
    
    // End Filter Inventory

    return view('home');
  }

  public function inventoryDatatable(Request $request)
  {
    $model = $this->inventory::query();
    if ($request->filled("inventory_id")) {
      $model = $model->where("id", $request->inventory_id);
    }
    if ($request->filled("warehouse_id")) {
      $model = $model->whereHas("inventory_warehouses", function($q) use($request) {
        $q->where("warehouse_id", $request->warehouse_id);
      });
    }
    $model = $model->with(['inventory_warehouses', 'raw_material', 'item_material'])->get();
    return datatables()->of($model)
    ->addIndexColumn()
    ->addColumn("item_name", function($inventory) {
      if($inventory->type_inventory == $inventory::TYPE_INVENTORY_RAW) {
          $rawMaterial = $inventory->raw_material;
          return $rawMaterial->name . ' ' . $rawMaterial->material->name;
      }
      $itemMaterial = $inventory->item_material;
      return $itemMaterial->item->name . ' ' . $itemMaterial->material->name;
    })
    ->addColumn("item_thick", function($inventory) {
      if($inventory->type_inventory == $inventory::TYPE_INVENTORY_RAW) {
          $rawMaterial = $inventory->raw_material;
          return $rawMaterial->thick;
      }
      $itemMaterial = $inventory->item_material;
      return $itemMaterial->thick;
    })
    ->addColumn("az_utomo", function($inventory) {
      if($inventory->type_inventory == $inventory::TYPE_INVENTORY_RAW) {
          $itemAZ = $inventory->raw_material->raw_az ?? "-";
      } else {
        $itemAZ = $inventory->item_material->item->item_az ?? "-";
      }
      return $itemAZ;
    })
    ->addColumn("grade", function($inventory) {
      if($inventory->type_inventory == $inventory::TYPE_INVENTORY_RAW) {
          $itemGrade = $inventory->raw_material->raw_grade ?? "-";
      } else {
        $itemGrade = $inventory->item_material->item->item_grade ?? "-";
      }
      return $itemGrade;
    })
    ->addColumn("berat", function($inventory) {
      if($inventory->type_inventory == $inventory::TYPE_INVENTORY_RAW) {
          $rawMaterial = $inventory->raw_material;
          return $rawMaterial->weight ?? "-";
      }
      $itemMaterial = $inventory->item_material;
      return $itemMaterial->weight ?? "-";
    })
    ->addColumn("stock_planning", function($inventory) {
      if($inventory->type_inventory == $inventory::TYPE_INVENTORY_RAW) {
          $itemMinStock = $inventory->raw_material->stock_planning ?? 0;
      } else {
        $itemMinStock = $inventory->item_material->item->stock_planning ?? 0;
      }
      return $itemMinStock;
    })
    ->addColumn("min_stock", function($inventory) {
      if($inventory->type_inventory == $inventory::TYPE_INVENTORY_RAW) {
          $rawMaterial = $inventory->raw_material;
          return $rawMaterial->min_stock ?? "-";
      } else {
        $itemMaterial = $inventory->item_material->item;
        return $itemMaterial->min_stock ?? "-";
      }
    })
    ->addColumn("posisi_stock", function($inventory) {
      return $inventory->stock;
    })
    ->addColumn("status_stock", function($inventory) {
      if($inventory->type_inventory == $inventory::TYPE_INVENTORY_RAW) {
          $item = $inventory->raw_material;
      } else {
        $item = $inventory->item_material->item;
      }

      if ($inventory->stock < $item->min_stock) {
        return "<span class='badge label-info'>Under</span>";
      } elseif ($inventory->stock >= $item->min_stock && $inventory->stock <= $item->stock_planning) {
        return "<span class='badge label-success'>Aman</span>";
      }
      elseif ($inventory->stock > $item->stock_planning) {
        return "<span class='badge label-danger'>Over</span>";
      }
    })
    ->addColumn("outstanding_po", function($inventory) {
      if ($inventory->type_inventory == $inventory::TYPE_INVENTORY_RAW) {
        $item = $inventory->raw_material;
        $open_po = $this->purchase->where("order_status", 1)->whereHas("purchase_details", function($q) use($item) {
          $q->where("raw_material_id", $item->id);
        })->count();
        return $open_po ?? 0;
      }
      return "-";
    })
    ->addColumn("rencana_order", function($inventory) {
      if ($inventory->type_inventory == $inventory::TYPE_INVENTORY_RAW) {
        $item = $inventory->raw_material;
        $open_po = $this->purchase->where("order_status", 1)->whereHas("purchase_details", function($q) use($item) {
          $q->where("raw_material_id", $item->id);
        })->count();
        $plan = $item->stock_planning;
        return ($plan - $inventory->stock + $open_po) > 0 ? $plan - $inventory->stock + $open_po : 0;
      }
      else {
        $item = $inventory->item_material->item;
        $plan = $item->stock_planning;
        return ($plan - $inventory->stock) > 0 ? $plan - $inventory->stock : 0;
      }
    })
    ->addColumn("catatan", function($inventory) {
      return "-";
    })
    ->rawColumns(['status_stock'])
    ->make(true);
  }
}
