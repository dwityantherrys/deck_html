<?php

namespace App\Models\Production;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class JobOrderDetail extends Model
{
    use SoftDeletes;
    
    const STATUS_PENDING = 0; 
    const STATUS_PROCESS = 1; 
    const STATUS_PARTIAL = 2; 
    const STATUS_FINISH = 3; 
    const STATUS_CANCEL = 4;

    protected $fillable = [
      'job_order_id',
      'sales_detail_id',
      'item_material_id',
      'status',
      'is_custom_length',
      'length',
      'sheet',
      'quantity',
      'balance',
      'balance_issued',
      'price',
      'amount',
      'total_price',
      'item_name',
      'created_at',
      'updated_at'
    ];

    // protected $appends = ['quantity_left'];

    // public function getQuantityLeftAttribute ()
    // {
    //   $quantity = $this->attributes['quantity'];
    //   $quantityGI = $this->good_issued_details()->sum('quantity');

    //   return $quantity - $quantityGI;
    // }

    public function good_issued_details ()
    {
      return $this->hasMany('App\Models\Production\GoodIssuedDetail', 'job_order_detail_id');
    }
    public function job_order ()
    {
      return $this->belongsTo('App\Models\Production\JobOrder', 'job_order_id');
    }
    public function sales_detail ()
    {
      return $this->belongsTo('App\Models\Sales\SalesDetail', 'sales_detail_id');
    }
    public function item_material ()
    {
      return $this->belongsTo('App\Models\Master\Item\Item', 'item_material_id');
    }
}
