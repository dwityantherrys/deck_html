<?php

namespace App\Models\Production;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class GoodReceipt extends Model
{
    use SoftDeletes;
    
    const STATUS_RECEIPT_PENDING = 0; 
    const STATUS_RECEIPT_FINISH = 1;

    protected $fillable = [
        'number',
        'date',
        'status',
        'warehouse_id',
        'factory_id',
        'good_issued_id',
        'created_by',
        'updated_by'
    ];

    protected $dates = ['created_at', 'updated_at', 'date'];
    protected $appends = ['date_formated'];

    public function getDateFormatedAttribute ()
    {
      return date('m/d/Y', strtotime($this->attributes['date']));
    }

    public function good_receipt_details ()
    {
      return $this->hasMany('App\Models\Production\GoodReceiptDetail', 'good_receipt_id');
    }
    public function warehouse ()
    {
      return $this->belongsTo('App\Models\Master\Warehouse', 'warehouse_id');
    }
    public function factory ()
    {
      return $this->belongsTo('App\Models\Master\Warehouse', 'factory_id');
    }
    public function good_issued ()
    {
      return $this->belongsTo('App\Models\Production\GoodIssued', 'good_issued_id');
    }
    public function pic ()
    {
      return $this->belongsTo('App\User', 'created_by');
    }

    public function log_print ()
    {
      return $this->hasOne('App\Models\LogPrint', 'transaction_number', 'number')->where('transaction_code', \Config::get('transactions.good_receipt.code'));
    }
}
