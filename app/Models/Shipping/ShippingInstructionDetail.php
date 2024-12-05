<?php

namespace App\Models\Shipping;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class ShippingInstructionDetail extends Model
{
    // use SoftDeletes;
    protected $fillable = [
        'shipping_instruction_id',
        'purchase_detail_id',
        'quantity'
    ];

    protected $appends = ['quantity_left'];

    public function getQuantityLeftAttribute ()
    {
      $quantity = $this->attributes['quantity'];
      $quantityDN = $this->purchase_detail()->sum('quantity');

      return $quantity - $quantityDN;
    }

    public function delivery_note_details()
    {
        return $this->hasMany('App\Models\Shipping\DeliveryNoteDetail', 'shipping_instruction_detail_id');
    }
    public function sales_detail()
    {
        return $this->belongsTo('App\Models\Sales\SalesDetail', 'sales_detail_id');
    }
    public function purchase_detail()
    {
        return $this->belongsTo('App\Models\Purchase\PurchaseDetail', 'purchase_detail_id');
    }
    public function good_receipt_detail()
    {
        return $this->belongsTo('App\Models\Sales\GoodReceiptDetail', 'good_receipt_detail_id');
    }
}
