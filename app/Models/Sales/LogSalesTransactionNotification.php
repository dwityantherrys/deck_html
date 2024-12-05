<?php

namespace App\Models\Sales;

use Illuminate\Database\Eloquent\Model;

class LogSalesTransactionNotification extends Model
{
   protected $fillable = ['sales_transaction_id', 'status', 'notification'];

   public function sales_transaction ()
   {
   		return $this->belongsTo('App\Models\Sales\SalesTransaction', 'sales_transaction_id');
   }
}
