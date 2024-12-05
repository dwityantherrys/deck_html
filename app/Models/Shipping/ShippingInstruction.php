<?php

namespace App\Models\Shipping;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class ShippingInstruction extends Model
{
    use SoftDeletes;

    const METHOD_IS_PICKUP = 1;
    const METHOD_IS_PICKUP_POINT = 2;
    const METHOD_IS_DELIVERY = 3;

    const NOT_RELEASE = 0;
    const RELEASE = 1;

    protected $fillable = [
        'purchase_id',
        'date',
        'number',
        'status',
        'remark',
        'received',
        'branch_id',
    ];

    protected $dates = ['created_at', 'updated_at', 'date'];
    protected $appends = ['date_formated'];

    public function getDateFormatedAttribute()
    {
        return isset($this->attributes['date']) ? \Carbon\Carbon::parse($this->attributes['date'])->format('d-m-Y') : null;
    }

    public function shipping_instruction_details()
    {
        return $this->hasMany('App\Models\Shipping\ShippingInstructionDetail', 'shipping_instruction_id');
    }
    public function delivery_note()
    {
        return $this->hasOne('App\Models\Shipping\DeliveryNote', 'shipping_instruction_id');
    }
    public function delivery_notes()
    {
        return $this->hasMany('App\Models\Shipping\DeliveryNote', 'shipping_instruction_id');
    }
    
    public function purchase()
    {
        return $this->belongsTo('App\Models\Purchase\Purchase', 'purchase_id');
    }
    
    public function address()
    {
        return $this->belongsTo('App\Models\Master\Profile\ProfileAddress', 'address_id');
    }
    
    public function branch()
    {
        return $this->belongsTo('App\Models\Master\Branch', 'branch_id');
    }

    public function log_print ()
    {
      return $this->hasOne('App\Models\LogPrint', 'transaction_number', 'number')->where('transaction_code', \Config::get('transactions.shipping_instruction.code'));
    }
}
