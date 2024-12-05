<?php

namespace App\Models\Purchase;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class PurchaseDetail extends Model
{
    use SoftDeletes;

    const REQUEST_PENDING = 0;
    const REQUEST_ACCEPT = 1;
    const REQUEST_REJECT = 2;

    const DEFAULT_ORDER_STATUS = 0; //PENDING

    protected $fillable = [
      'request_status',
      'order_status',
      'raw_material_id',
      'item_material_id',
      'item_name',
      'quantity',
      'estimation_price',
      'amount',
      'up',
      'purchase_id',
    ];

    protected $appends = ['quantity_left'];

    public function getQuantityLeftAttribute ()
    {
      $quantity = $this->attributes['quantity'];
      // $quantityReceipt = $this->receipt_details()->sum('quantity');
      $quantityReceipt = $this->receipt_details()->sum('quantity');

      // Debugging: lihat hasil relasi
      // dd($this->receipt_details());

      return $quantity - $quantityReceipt;
    }

    public function receipt_details ()
    {
      return $this->hasMany('App\Models\Purchase\PurchaseReceiptDetail', 'purchase_detail_id');
    }

    public function purchase ()
    {
      return $this->belongsTo('App\Models\Purchase\Purchase', 'purchase_id');
    }
    
    public function raw_material ()
    {
      return $this->belongsTo('App\Models\Master\Material\RawMaterial', 'raw_material_id');
    }
    public function item_material ()
    {
      return $this->belongsTo('App\Models\Master\Item\ItemMaterial', 'item_material_id');
    }
}
