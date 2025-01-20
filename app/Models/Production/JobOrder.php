<?php

namespace App\Models\Production;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class JobOrder extends Model
{
    use SoftDeletes;
    
    const TYPE_SALES = 0; 
    const TYPE_PRODUCTION = 1;
    const STATUS_PENDING = 0; 
    const STATUS_PROCESS = 1; 
    const STATUS_PARTIAL = 2; 
    const STATUS_FINISH = 3; 
    const STATUS_CANCEL = 4;

    const TYPE_IT = 1;
    const TYPE_VEHICLE = 2;
    const TYPE_AC = 3;
    const TYPE_BUILDING = 4;
    const TYPE_ELECTRONIC = 5;
    const TYPE_CABLE = 6;
    const TYPE_OTHERS = 7;

    protected $fillable = [
        'name',
        'number',
        'date',
        'due_date',
        'status',
        'type',
        'total_price',
        'amount_tax',
        'remark',
        'tax_type',
        'warehouse_id',
        'vendor_id',
        'location',
        'created_by'
    ];

    protected $dates = ['created_at', 'updated_at', 'date', 'due_date'];
    protected $appends = ['date_formated', 'due_date_formated'];

    public function getDateFormatedAttribute ()
    {
      return date('m/d/Y', strtotime($this->attributes['date']));
    }

    public function getDueDateFormatedAttribute ()
    {
      if(empty($this->attributes['due_date'])) return;

      return date('m/d/Y', strtotime($this->attributes['due_date']));
    }

    public function job_order_details ()
    {
      return $this->hasMany('App\Models\Production\JobOrderDetail', 'job_order_id');
    }
    public function sales ()
    {
      return $this->belongsTo('App\Models\Sales\Sales', 'sales_id');
    }
    public function pic ()
    {
      return $this->belongsTo('App\User', 'created_by');
    }
    public function vendor ()
    {
      return $this->belongsTo('App\User', 'vendor_id');
    }

    public function log_print ()
    {
      return $this->hasOne('App\Models\LogPrint', 'transaction_number', 'number')->where('transaction_code', \Config::get('transactions.job_order.code'));
    }
}
