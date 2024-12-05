<?php

namespace App\Models\Purchase;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class PurchaseReceiptDetail extends Model
{
    use SoftDeletes;

    const RECEIPT_PARTIAL = 0;
    const RECEIPT_FULL = 1;

    protected $table = 'purchase_receive_details';
    protected $fillable = [
      'status',
      'purchase_detail_id', 
      'purchase_receipt_id', 
      'quantity', 
      'estimation_price',
      'discount',
      'amount'
    ];

    public function purchase_receipt ()
    {
      return $this->belongsTo('App\Models\Purchase\PurchaseReceipt', 'purchase_receipt_id');
    }
    public function purchase_detail ()
    {
      return $this->belongsTo('App\Models\Purchase\PurchaseDetail', 'purchase_detail_id');
    }
}
