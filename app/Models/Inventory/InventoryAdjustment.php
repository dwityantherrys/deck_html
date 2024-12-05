<?php

namespace App\Models\Inventory;

use Illuminate\Database\Eloquent\Model;

class InventoryAdjustment extends Model
{
    protected $fillable = [
        'inventory_warehouse_id', 
        'stock_before_adjustment', 
        'stock_after_adjustment', 
        'cost_of_good_before_adjustment', 
        'cost_of_good_after_adjustment', 
        'created_by'
    ];

    public function inventory_warehouse()
    {
        return $this->belongsTo('App\Models\Inventory\InventoryWarehouse', 'inventory_warehouse_id');
    }
    public function pic()
    {
        return $this->belongsTo('App\User', 'created_by');
    }
}
