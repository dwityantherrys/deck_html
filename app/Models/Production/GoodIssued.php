<?php

namespace App\Models\Production;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class GoodIssued extends Model
{
    use SoftDeletes;
    
    const STATUS_PENDING = 0; 
    const STATUS_PROCESS = 1; 
    const STATUS_PARTIAL = 2; //shipping partial
    const STATUS_FINISH = 3; //shipping finish
    const STATUS_CANCEL = 4;

    protected $fillable = [
        'number',
        'date',
        'status',
        'warehouse_id',
        'factory_id',
        'job_order_id',
        'created_by',
        'updated_by'
    ];

    protected $dates = ['created_at', 'updated_at', 'date'];
    protected $appends = ['date_formated'];

    public function getDateFormatedAttribute ()
    {
      return date('m/d/Y', strtotime($this->attributes['date']));
    }

    public function good_issued_details ()
    {
      return $this->hasMany('App\Models\Production\GoodIssuedDetail', 'good_issued_id');
    }
    public function warehouse ()
    {
      return $this->belongsTo('App\Models\Master\Warehouse', 'warehouse_id');
    }
    public function factory ()
    {
      return $this->belongsTo('App\Models\Master\Warehouse', 'factory_id');
    }
    public function job_order ()
    {
      return $this->belongsTo('App\Models\Production\JobOrder', 'job_order_id');
    }
    public function pic ()
    {
      return $this->belongsTo('App\User', 'created_by');
    }

    public function log_print ()
    {
      return $this->hasOne('App\Models\LogPrint', 'transaction_number', 'number')->where('transaction_code', \Config::get('transactions.good_issued.code'));
    }
}
