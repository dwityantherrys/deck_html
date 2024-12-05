<?php

namespace App\Models\Inventory\ReturnLoan;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class ReturnLoan extends Model
{
  protected $table = 'asset_loans';
    use SoftDeletes;
  
    const LOAN_REQUEST = 0;
    const LOAN_ACCEPT = 1;
    const LOAN_REJECT = 2;

    protected $fillable = [
        'loan_number',
        'loan_date',
        'loan_status',
        'loan_expiration_date',
        'loan_canceled_reason',
        'customer_id',
        'warehouse_id',
        'created_by'
    ];

    protected $dates = ['created_at', 'updated_at', 'loan_date', 'loan_expiration_date'];
    protected $appends = ['loan_date_formated', 'loan_expiration_date_formated'];

    public function getLoanDateFormatedAttribute ()
    {
      return date('m/d/Y', strtotime($this->attributes['loan_date']));
    }
    public function getLoanExpirationDateFormatedAttribute ()
    {
      return date('m/d/Y', strtotime($this->attributes['loan_expiration_date']));
    }

    public function warehouse_out ()
    {
      return $this->belongsTo('App\Models\Master\Warehouse', 'warehouse_id');
    }
    public function customer ()
    {
      return $this->belongsTo('App\User', 'customer_id');
    }
    public function pic ()
    {
      return $this->belongsTo('App\User', 'created_by');
    }
    public function loan_details ()
    {
      return $this->hasMany('App\Models\Inventory\Loan\LoanDetail', 'asset_loan_id');
    }
}
