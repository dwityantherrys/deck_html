<?php

namespace App\Models\Purchase;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class PurchaseInvoice extends Model
{
    use SoftDeletes;

    const BILL_PENDING = 0;
    const BILLED = 1;
    const PAID_OFF = 2;
    
    const TAX_NONE = 0;
    const TAX_11 = 1;
    const TAX_INCLUDE = 2;


    protected $fillable = [
        'date_of_issued',
        'due_date',
        'paid_date',
        'discount',
        'downpayment',
        'status',
        'purchase_order_id',
        'bill',
        'balance',
        'use_tax',
        'tax_type',
        'number',
        'amount_tax',
        'amount_discount'
    ];

    protected $dates = ['created_at', 'updated_at', 'date_of_issued', 'due_date', 'paid_date'];
    protected $appends = ['date_of_issued_formated', 'due_date_formated', 'paid_date_formated'];

    public function getDateOfIssuedFormatedAttribute ()
    {
      return date('m/d/Y', strtotime($this->attributes['date_of_issued']));
    }
    public function getDueDateFormatedAttribute ()
    {
      return date('m/d/Y', strtotime($this->attributes['due_date']));
    }
    public function getPaidDateFormatedAttribute ()
    {
      return date('m/d/Y', strtotime($this->attributes['paid_date']));
    }

    public function purchase_order ()
    {
        return $this->belongsTo('App\Models\Purchase\Purchase', 'purchase_order_id');
    }

    public function log_print ()
    {
      return $this->hasOne('App\Models\LogPrint', 'transaction_number', 'number')->where('transaction_code', \Config::get('transactions.purchase_invoice.code'));
    }
}
