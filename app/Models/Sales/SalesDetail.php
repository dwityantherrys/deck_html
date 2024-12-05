<?php

namespace App\Models\Sales;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class SalesDetail extends Model
{
    use SoftDeletes;
    
    const REQUEST_PENDING = 0;
    const REQUEST_ACCEPT = 1;
    const REQUEST_REJECT = 2;

    const DEFAULT_ORDER_STATUS = 0; //PENDING

    protected $fillable = [
        'quotation_status',
        'order_status',
        'sales_id',
        'item_material_id',
        'is_custom_length',
        'length',
        'height',
        'width',
        'sheet',
        'item_name',
        'quantity',
        'estimation_price',
        'amount',
        'discount',
        'tax',
        'price',
        'total_price',
        'summary'
    ];

    protected $appends = ['quantity_left'];

    public function getQuantityLeftAttribute ()
    {
      $quantity = $this->attributes['quantity'];
      $quantityJO = $this->job_order_details()->sum('quantity');

      return $quantity - $quantityJO;
    }

    public function sales ()
    {
      return $this->belongsTo('App\Models\Sales\Sales', 'sales_id');
    }
    public function job_order_details ()
    {
      return $this->hasMany('App\Models\Production\JobOrderDetail', 'sales_detail_id');
    }
    public function item_material ()
    {
      return $this->belongsTo('App\Models\Master\Item\ItemMaterial', 'item_material_id');
    }
    public function item_review ()
    {
      return $this->hasOne('App\Models\Master\Item\ItemReview', 'sales_detail_id');
    }
}
