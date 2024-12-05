<?php

namespace App\Models\Asset;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class AssetLoanDetail extends Model
{
    use SoftDeletes;
    
    const REQUEST_PENDING = 0;
    const REQUEST_ACCEPT = 1;
    const REQUEST_REJECT = 2;

    const DEFAULT_ORDER_STATUS = 0; //PENDING

    protected $fillable = [
        'loan_status',
        'asset_stock_id',
        'quantity'
    ];

    // protected $appends = ['quantity_left'];

    // public function getQuantityLeftAttribute ()
    // {
    //   $quantity = $this->attributes['quantity'];
    //   $quantityJO = $this->job_order_details()->sum('quantity');

    //   return $quantity - $quantityJO;
    // }

    // public function loan ()
    // {
    //   return $this->belongsTo('App\Models\Sales\Sales', 'sales_id');
    // }
    public function asset_stock ()
    {
      return $this->belongsTo('App\Models\Asset\AssetStock', 'asset_stock_id');
    }
}
