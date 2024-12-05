<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class LogPrint extends Model
{
    protected $fillable = ['transaction_code', 'transaction_number', 'employee_id', 'date'];
    protected $dates = ['created_at', 'updated_at', 'date'];

    protected $appends = ['date_formated'];

    public function getDateFormatedAttribute ()
    {
      return date('m/d/Y', strtotime($this->attributes['date']));
    }

    public function employee ()
    {
        return $this->belongsTo('App\User', 'employee_id');
    }
}
