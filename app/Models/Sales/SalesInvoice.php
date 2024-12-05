<?php

namespace App\Models\Sales;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class SalesInvoice extends Model
{
    // use SoftDeletes;
    
    const PENDING = 0;    
    const BILLED = 1;
    const PAID_OFF = 2;

    protected $fillable = [
        'delivery_note_id',
        'payment_method_id',
        'due_date',
        'paid_of_date',
        'status',
        'total_quantity',
        'shipping_cost',
        'discount',
        'total_bill',
        'number'
    ];

    protected $dates = ['created_at', 'updated_at', 'due_date', 'paid_of_date'];
    protected $appends = ['due_date_formated', 'paid_of_date_formated'];

    public function getDueDateFormatedAttribute ()
    {
      return date('m/d/Y', strtotime($this->attributes['due_date']));
    }
    public function getPaidOfDateFormatedAttribute ()
    {
      return date('m/d/Y', strtotime($this->attributes['paid_of_date']));
    }

    public function delivery_note ()
    {
        return $this->belongsTo('App\Models\Shipping\DeliveryNote', 'delivery_note_id');
    }
    public function payment_method ()
    {
        return $this->belongsTo('App\Models\Master\Payment\PaymentMethod', 'payment_method_id');
    }

    public function log_print ()
    {
      return $this->hasOne('App\Models\LogPrint', 'transaction_number', 'number')->where('transaction_code', \Config::get('transactions.sales_invoice.code'));
    }
}
