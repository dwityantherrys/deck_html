<?php

namespace App\Models\Mobile\Voucher;

use Illuminate\Database\Eloquent\Model;

class VoucherUsage extends Model
{
    const STATUS_AVAILABLE = 0;
    const STATUS_USED = 1;
    const STATUS_EXPIRED = 2;

    protected $fillable = [
        'voucher_id',
        'user_id',
        'sales_id',
        'status_usage'
    ];

    public function voucher ()
    {
        return $this->belongsTo('App\Models\Mobile\Voucher\Voucher', 'voucher_id');
    }
    public function customer ()
    {
        return $this->belongsTo('App\User', 'user_id');
    }
    public function sales ()
    {
        return $this->belongsTo('App\Models\Sales\Sales', 'sales_id');
    }
}
