<?php

namespace App\Models\Production;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class GoodReceiptDetail extends Model
{
    use SoftDeletes;
    
    const STATUS_NOT_DEFECT = 0; 
    const STATUS_DEFECT = 1;

    protected $fillable = [
      'good_receipt_id',
      'job_order_detail_id',
      'item_material_id',
      'inventory_warehouse_id',
      'quantity',
      'is_defect',
      'created_at',
      'updated_at'
    ];

    protected $appends = ['quantity_left'];

    public function getQuantityLeftAttribute ()
    {
      $quantity = $this->attributes['quantity'];
      $quantitySI = $this->shipping_instruction_details()->sum('quantity');

      return $quantity - $quantitySI;
    }

    public function good_receipt_consumes ()
    {
      return $this->hasMany('App\Models\Production\GoodReceiptConsume', 'good_receipt_detail_id');
    }
    public function shipping_instruction_details ()
    {
      return $this->hasMany('App\Models\Shipping\ShippingInstructionDetail', 'good_receipt_detail_id');
    }
    public function job_order_detail ()
    {
      return $this->belongsTo('App\Models\Production\JobOrderDetail', 'job_order_detail_id');
    }
    public function item_material ()
    {
      return $this->belongsTo('App\Models\Master\Item\ItemMaterial', 'item_material_id');
    }
    public function inventory_warehouse ()
    {
      return $this->belongsTo('App\Models\Inventory\InventoryWarehouse', 'inventory_warehouse_id');
    }
}
