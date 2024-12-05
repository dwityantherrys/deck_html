<?php

namespace App\Models\Master\Profile;

use Illuminate\Database\Eloquent\Model;

class ProfileTransactionSetting extends Model
{
    const DEFAULT_PAYMENT_METHOD_WEB = 1; //CASH
    const DEFAULT_PAYMENT_METHOD_MOBILE = 2; //TRANSFER
    const DEFAULT_TEMPO_TYPE = 'NOT_USED';
    
    const TEMPO_TYPE = [
      'NOT_USED'  => [],
      'DAY'       => [ 'OPTIONS'  => 365, 'EXTEND' => 0 ],
      'MONTH'     => [ 'OPTIONS'  => 12,  'EXTEND' => 30],
      'WEEK'      => [ 'OPTIONS'  => 4,   'EXTEND' => 7 ]
    ];

    protected $fillable = [
      'tempo_type',
      'tempo_charge_day',
      'tempo_charge_week',
      'tempo_charge_month',      
      'limit',
      'markdown_sales',
      'markdown_purchase',
      'payment_method_id',
      'profile_id',      
      'created_by'
    ];

    public function profile ()
    {
      return $this->belongsTo('App\Models\Master\Profile\Profile', 'profile_id');
    }

    public function payment_method ()
    {
      return $this->belongsTo('App\Models\Master\Payment\PaymentMethod', 'payment_method_id');
    }
}
