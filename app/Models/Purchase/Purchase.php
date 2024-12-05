<?php

namespace App\Models\Purchase;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Purchase extends Model
{
    use SoftDeletes;

    const TYPE_PURCHASE = 0;
    const TYPE_UMUM = 1;
    const TYPE_PRODUKSI = 2;
    const TYPE_LIGHTING = 3;
    const TYPE_PJUTS = 4;

    const TYPE_IT = 1;
    const TYPE_HR_GA = 2;
    const TYPE_OPERASIONAL = 3;
    const TYPE_OFFICE_SUPPLIES = 4;
    const TYPE_VEHICLE = 5;
    const TYPE_OTHERS = 6;
    
    const TAX_NONE = 0;
    const TAX_11 = 1;
    const TAX_INCLUDE = 2;

    const REQUEST_PENDING = 0;
    const REQUEST_ACCEPT = 1;
    const REQUEST_REJECT = 2;

    const DEFAULT_ORDER_STATUS = 0; //PENDING

    const ORDER_PENDING = 0;
    const ORDER_PROCESS = 1;
    const ORDER_FINISH = 2;
    const ORDER_CANCEL = 3;

    const HEAD = 0;
    const BRANCH = 1;

    protected $fillable = [
      'request_date',
      'request_number',
      'request_status',
      'request_type',
      'pat_number',
      'warehouse_id',
      'branch_id',
      'sales_id',
      'order_date',
      'order_number',
      'order_status',
      'vendor_id',
      'total_price',
      'request_by',
      'is_active',
      'item_name',
      'tax_type',
      'destination',
      'amount_tax',
      'remark',
    ];

    protected $dates = ['created_at', 'updated_at', 'request_date', 'order_date'];
    protected $appends = ['request_date_formated', 'order_date_formated'];

    public function getRequestDateFormatedAttribute ()
    {
      return date('m/d/Y', strtotime($this->attributes['request_date']));
    }
    public function getOrderDateFormatedAttribute ()
    {
      return date('m/d/Y', strtotime($this->attributes['order_date']));
    }

    public function warehouse_receiver ()
    {
      return $this->belongsTo('App\Models\Master\Warehouse', 'warehouse_id');
    }
    public function branch ()
    {
      return $this->belongsTo('App\Models\Master\Branch', 'branch_id');
    }
    public function vendor ()
    {
      return $this->belongsTo('App\User', 'vendor_id');
    }
    public function pic ()
    {
      return $this->belongsTo('App\User', 'request_by');
    }
    public function purchase_details ()
    {
      return $this->hasMany('App\Models\Purchase\PurchaseDetail', 'purchase_id');
    }
    public function account_payables ()
    {
      return $this->hasMany('App\Models\Finance\AccountPayable', 'purchase_id');
    }

    public function request_log_print ()
    {
      return $this->hasOne('App\Models\LogPrint', 'transaction_number', 'request_number')->where('transaction_code', \Config::get('transactions.purchase_request.code'));
    }
    public function order_log_print ()
    {
      return $this->hasOne('App\Models\LogPrint', 'transaction_number', 'order_number')->where('transaction_code', \Config::get('transactions.purchase_order.code'));
    }

    public function journal() {
      return $this->hasMany("App\Models\Finance\FinanceJournal", "ref", "order_number");
    }
}
