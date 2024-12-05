<?php

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

use App\Models\Master\Warehouse;
use App\Models\Master\Material\Color;
use App\Models\Master\Material\Material;
use App\Models\Master\Item\ItemCategory;
use App\Models\Master\Item\ItemLength;
use App\Models\Master\Item\Item;
use App\Models\Master\Item\ItemMaterial;
use App\Models\Inventory\Inventory;
use App\Models\Inventory\InventoryWarehouse;
use App\Models\Inventory\InventoryAdjustment;

class DummyInventoryTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        DB::statement('SET FOREIGN_KEY_CHECKS = 0'); // disable foreign key constraints

	    DB::beginTransaction();

	    Warehouse::truncate();
	    Color::truncate();
        Material::truncate();
        ItemCategory::truncate();
        ItemLength::truncate();

        $warehouse = Warehouse::create([
            'name' => 'SBY WH',
            'address' => 'Surabaya',
            'type' => 0,
            'is_active' => true,
            'region_type' => 0,
            'region_id' => 444
        ]);

        $color = Color::create([ 'code' => '#ff0028', 'name' => 'red', 'is_active' => true ]);

        $material = Material::create([ 'name' => 'Galvalum', 'is_active' => true ]);

        $itemCategory = ItemCategory::create([ 'name' => 'Atap', 'is_active' => true ]);

        ItemLength::insert([
            ['name' => 'standart', 'length' => 1, 'is_active' => true],
            ['name' => 'long', 'length' => 2, 'is_active' => true],
            ['name' => 'x-long', 'length' => 3, 'is_active' => true]
        ]);

        $item = Item::create([
            'name' => 'Utomo Atap',
            'height' => 100,
            'width' => 100,
            'length' => 0,
            'item_category_id' => $itemCategory->id,
            'is_active' => true,
            'has_length_options' => true
        ]);
        $itemMaterial = $item->item_materials()->create([
            'thick' => 0.45,
            'weight' => 0.8,
            'color_id' => $color->id,
            'material_id' => $material->id,
            'is_default' => true,
            'is_active' => true
        ]);

        $inventory = Inventory::create([
            'type_inventory' => 1,
            'reference_id' => $itemMaterial->id,
            'cost_of_good' => 0,
            'stock' => 100
        ]);
        $inventoryWarehouse = $inventory->inventory_warehouses()->create([
            'warehouse_id' => $warehouse->id,
            'selling_price' => 300000,
            'stock' => 100
        ]);
        $inventoryWarehouse->inventory_adjustments()->create([
            'stock_before_adjustment' => 0,
            'stock_after_adjustment' => 100,
            'created_by' => 1 //utomodeck administrator
        ]);

        DB::commit();
		
		DB::statement('SET FOREIGN_KEY_CHECKS = 1'); // enable foreign key constraints
    }
}
