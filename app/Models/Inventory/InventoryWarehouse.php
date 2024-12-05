<?php

namespace App\Models\Inventory;

use Illuminate\Database\Eloquent\Model;

class InventoryWarehouse extends Model
{
    protected $fillable = [
        'inventory_id',
        'inventory_warehouse_number',
        'receipt_detail_id',
        'warehouse_id',
        'selling_price',
        'stock',
    ];

    public function last_adjustment()
    {
        return $this->hasOne('App\Models\Inventory\InventoryAdjustment', 'inventory_warehouse_id')->latest();
    }
    public function inventory()
    {
        return $this->belongsTo('App\Models\Inventory\Inventory', 'inventory_id');
    }
    public function inventory_adjustments()
    {
        return $this->hasMany('App\Models\Inventory\InventoryAdjustment', 'inventory_warehouse_id');
    }
    public function warehouse()
    {
        return $this->belongsTo('App\Models\Master\Warehouse', 'warehouse_id');
    }
    public function receipt_detail()
    {
        return $this->belongsTo('App\Models\Purchase\PurchaseReceipt', 'receipt_detail_id');
    }
}
