<?php

namespace App\Models\Purchase;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class PurchaseReceipt extends Model
{
    use SoftDeletes;

    const RECEIPT_PROCESS = 0;
    const RECEIPT_PARTIAL = 1;
    const RECEIPT_FULL = 2;

    protected $table = 'purchase_receives';

    protected $fillable = [
      'date',
      'number',
      'status',
      'purchase_id',
      'discount',
      'total_price',
      'receive_by',
    ];

    protected $dates = ['created_at', 'updated_at', 'date'];
    protected $appends = ['date_formated'];

    public function getDateFormatedAttribute ()
    {
      return date('m/d/Y', strtotime($this->attributes['date']));
    }

    public function pic ()
    {
      return $this->belongsTo('App\User', 'receive_by');
    }
    public function purchase ()
    {
      return $this->belongsTo('App\Models\Purchase\Purchase', 'purchase_id');
    }
    public function inventory_warehouses ()
    {
      return $this->hasMany('App\Models\Inventory\InventoryWarehouse', 'receipt_detail_id');
    }
    public function receipt_details ()
    {
      return $this->hasMany('App\Models\Purchase\PurchaseReceiptDetail', 'purchase_receive_id');
    }

    public function log_print ()
    {
      return $this->hasOne('App\Models\LogPrint', 'transaction_number', 'number')->where('transaction_code', \Config::get('transactions.purchase_receipt.code'));
    }

    public function purchase_invoice ()
    {
      return $this->hasOne("App\Models\Purchase\PurchaseInvoice", "purchase_receive_id");
    }
}
