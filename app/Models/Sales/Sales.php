<?php

namespace App\Models\Sales;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

use App\Models\Mobile\Voucher\VoucherUsage;

class Sales extends Model
{
    use SoftDeletes;

    const TRANSACTION_CHANNEL_WEB = 0;
    const TRANSACTION_CHANNEL_MOBILE = 1;

    const QUOTATION_PENDING = 0;
    const QUOTATION_ACCEPT = 1;
    const QUOTATION_REJECT = 2;

    const DEFAULT_ORDER_STATUS = 0; //PENDING

    const ORDER_PENDING = 0;
    const ORDER_PROCESS = 1;
    const ORDER_FINISH = 2;
    const ORDER_CANCEL = 3;

    protected $fillable = [
        'quotation_number',
        'quotation_date',
        'quotation_status',
        'quotation_expiration_date',
        'quotation_canceled_reason',
        'order_number',
        'order_date',
        'order_status',
        'customer_id',
        'warehouse_id',
        'shipping_method_id',
        'shipping_address_id',
        'shipping_cost',
        'discount',
        'downpayment',
        'tax',
        'total_price',
        'grand_total_price',
        'payment_method_id',
        'payment_bank_channel_id',
        'transaction_channel',
        'created_by'
    ];

    protected $dates = ['created_at', 'updated_at', 'quotation_date', 'order_date'];
    protected $appends = ['quotation_date_formated', 'order_date_formated'];

    public function getQuotationDateFormatedAttribute ()
    {
      return date('m/d/Y', strtotime($this->attributes['quotation_date']));
    }
    public function getOrderDateFormatedAttribute ()
    {
      return date('m/d/Y', strtotime($this->attributes['order_date']));
    }

    public function warehouse_out ()
    {
      return $this->belongsTo('App\Models\Master\Warehouse', 'warehouse_id');
    }
    public function warehouse_pickup_point ()
    {
      return $this->belongsTo('App\Models\Master\Warehouse', 'shipping_address_id');
    }
    public function customer ()
    {
      return $this->belongsTo('App\User', 'customer_id');
    }
    public function customer_address ()
    {
      return $this->belongsTo('App\Models\Master\Profile\ProfileAddress', 'shipping_address_id');
    }
    public function payment_method ()
    {
      return $this->belongsTo('App\Models\Master\Payment\PaymentMethod', 'payment_method_id');
    }
    public function payment_bank_channel ()
    {
      return $this->belongsTo('App\Models\Master\Payment\PaymentBankChannel', 'payment_bank_channel_id');
    }
    public function pic ()
    {
      return $this->belongsTo('App\User', 'created_by');
    }
    public function sales_details ()
    {
      return $this->hasMany('App\Models\Sales\SalesDetail', 'sales_id');
    }
    public function shipping_instruction ()
    {
      return $this->hasOne('App\Models\Shipping\ShippingInstruction', 'sales_id');
    }
    public function shipping_instructions ()
    {
      return $this->hasMany('App\Models\Shipping\ShippingInstruction', 'sales_id');
    }
    public function account_receivables ()
    {
      return $this->hasMany('App\Models\Finance\AccountReceivable', 'sales_id');
    }
    public function voucher_usage ()
    {
      return $this->hasOne('App\Models\Mobile\Voucher\VoucherUsage', 'sales_id')->where('status_usage', VoucherUsage::STATUS_AVAILABLE);
    }

    public function quotation_log_print ()
    {
      return $this->hasOne('App\Models\LogPrint', 'transaction_number', 'quotation_number')->where('transaction_code', \Config::get('transactions.sales_quotation.code'));
    }
    public function order_log_print ()
    {
      return $this->hasOne('App\Models\LogPrint', 'transaction_number', 'order_number')->where('transaction_code', \Config::get('transactions.sales_order.code'));
    }

    public function getTableColumns() {
        return $this->getConnection()->getSchemaBuilder()->getColumnListing($this->getTable());
    }
}
