<?php

namespace App\Models\Shipping;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class DeliveryNoteDetail extends Model
{
    // use SoftDeletes;
    const DEFAULT_DELIVERY_STATUS = 0;

    const DELIVERY_PENDING = 0;
    const DELIVERY_PROCESS = 1;
    const DELIVERY_FINISH = 2;
    const DELIVERY_RETUR = 3;

    protected $fillable = [
        'delivery_note_id',
        'job_order_detail_id',
        'status'
    ];

    public function job_order_detail()
    {
        return $this->belongsTo('App\Models\Production\JobOrderDetail', 'job_order_detail_id');
    }
}
