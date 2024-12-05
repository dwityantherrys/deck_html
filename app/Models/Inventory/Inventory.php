<?php

namespace App\Models\Inventory;

use Illuminate\Database\Eloquent\Model;

class Inventory extends Model
{
    const TYPE_INVENTORY_RAW = 0;
    const TYPE_INVENTORY_FINISH = 1;

    protected $fillable = [
        'type_inventory',
        'reference_id',
        'cost_of_good',
        'stock',
    ];

    public function purchase_receipt ()
    {
      return $this->belongsTo('App\Models\Purchase\Purchase', 'purchase_id');
    }
    public function raw_material ()
    {
      return $this->belongsTo('App\Models\Master\Material\RawMaterial', 'reference_id');
    }
    public function item_material ()
    {
      return $this->belongsTo('App\Models\Master\Item\ItemMaterial', 'reference_id');
    }
    public function inventory_warehouses ()
    {
      return $this->hasMany('App\Models\Inventory\InventoryWarehouse', 'inventory_id');
    }
    public function inventory_movements ()
    {
      return $this->hasMany('App\Models\Inventory\InventoryMovement', 'inventory_id');
    }
}
