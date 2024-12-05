<?php

namespace App\Models\Mobile\Voucher;

use Illuminate\Database\Eloquent\Model;

class Voucher extends Model
{
    const TYPE_DISCOUNT_SALES = 0;

    const LIMIT_TYPE_ONCE = 0;
    const LIMIT_TYPE_DAILY = 1;
    const LIMIT_TYPE_WEEKLY = 2;
    const LIMIT_TYPE_MONTHLY = 3;

    protected $fillable = [
        'name',
        'code',
        'value',
        'value_type',
        'minimum_sales',
        'limit_type',
        'limit_usage',
        'limit_customer',
        'notes',
        'start_date',
        'expiration_date',
        'is_active',
    ];

    protected $dates = ['created_at', 'updated_at', 'start_date', 'expiration_date'];

    public function scopeActive($query)
    {
      return $query->where('is_active', 1);
    }
    
    public function voucher_usages()
    {
        return $this->hasMany('App\Models\Mobile\Voucher\VoucherUsage', 'voucher_id');
    }
}
