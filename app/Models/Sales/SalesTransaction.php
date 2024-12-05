<?php

namespace App\Models\Sales;

use Illuminate\Database\Eloquent\Model;

class SalesTransaction extends Model
{
    const TRANSACTION_PENDING = 0;
    const TRANSACTION_SUCCESS = 1;
    const TRANSACTION_FAILED = 2;
    const TRANSACTION_EXPIRED = 3;

    protected $fillable = [
        'code',
        'account_receivable_id',
        'sales_invoice_id',
        'amount',
        'status',
        'snap_token',
        'note'
    ];

    public function account_receivable()
    {
        return $this->belongsTo('App\Models\Finance\AccountReceivable', 'account_receivable_id');
    }
    public function sales_invoice()
    {
        return $this->belongsTo('App\Models\Sales\SalesInvoice', 'sales_invoice_id');
    }
    public function log_sales_transaction_notification ()
    {
        return $this->belongsTo('App\Models\Sales\LogSalesTransactionNotification', 'sales_transaction_id');
    }
}
