<?php

namespace App\Models\Master\Payment;

use Illuminate\Database\Eloquent\Model;

class PaymentMethod extends Model
{
    const AVAILABLE_AT_WEB = 0;
    const AVAILABLE_AT_APP = 1;
    const AVAILABLE_AT_BOTH = 2;

    protected $fillable = [
        'code', 'name', 'description', 'image', 'rekening_number', 
        'channel', 'has_code_rule', 'guide', 'available_at', 'is_active'
    ];

    protected $appends = ['image_url'];

    public function getImageUrlAttribute ()
    {
        $source = !empty($this->attributes['image']) ? "/storage/" . $this->attributes['image'] : "/img/no-image.png";
        return asset($source);
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', 1);
    }
    public function scopeAvailableOffline($query)
    {
        return $query->where('available_at', self::AVAILABLE_AT_WEB);
    }
}
