<?php

namespace App\Models\Production;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class GoodIssuedDetail extends Model
{
    use SoftDeletes;
    
    const STATUS_PENDING = 0; 
    const STATUS_PROCESS = 1; 
    const STATUS_PARTIAL = 2; 
    const STATUS_FINISH = 3; 
    const STATUS_CANCEL = 4;

    protected $fillable = [
      'good_issued_id',
      'job_order_detail_id',
      'raw_material_id',
      'inventory_warehouse_id',
      'status',
      'quantity',
      'balance',
      'created_at',
      'updated_at'
    ];

    public function good_receipt_consumes ()
    {
      return $this->hasMany('App\Models\Production\GoodReceiptConsume', 'good_issued_detail_id');
    }
    public function job_order_detail ()
    {
      return $this->belongsTo('App\Models\Production\JobOrderDetail', 'job_order_detail_id');
    }
    public function raw_material ()
    {
      return $this->belongsTo('App\Models\Master\Material\RawMaterial', 'raw_material_id');
    }
    public function inventory_warehouse ()
    {
      return $this->belongsTo('App\Models\Inventory\InventoryWarehouse', 'inventory_warehouse_id');
    }
}
