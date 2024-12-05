<?php

namespace App\Models\Shipping;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class DeliveryNote extends Model
{
    // use SoftDeletes;

    const METHOD_IS_PICKUP = 1;
    const METHOD_IS_PICKUP_POINT = 2;
    const METHOD_IS_DELIVERY = 3;

    const TYPE_PRODUCTION = 1;

    const DEFAULT_DELIVERY_STATUS = 0;

    const DELIVERY_PENDING = 0;
    const DELIVERY_PROCESS = 1;
    const DELIVERY_FINISH = 2;
    const DELIVERY_RETUR = 3;

    const TYPE_IT = 1;
    const TYPE_VEHICLE = 2;
    const TYPE_AC = 3;
    const TYPE_BUILDING = 4;
    const TYPE_ELECTRONIC = 5;
    const TYPE_CABLE = 6;
    const TYPE_OTHERS = 7;

    protected $fillable = [
        'job_order_id',
        'vendor_id',
        'date',
        'number',
        'status',
        'remark'
    ];

    protected $dates = ['created_at', 'updated_at', 'date'];
    protected $appends = ['date_formated'];

    public function getDateFormatedAttribute ()
    {
      return date('m/d/Y', strtotime($this->attributes['date']));
    }
    public function getDeliveryDateFormatedAttribute ()
    {
      return date('m/d/Y', strtotime($this->attributes['date']));
    }

    public function delivery_note_details()
    {
        return $this->hasMany('App\Models\Shipping\DeliveryNoteDetail', 'delivery_note_id');
    }
    public function sales_invoice()
    {
        return $this->hasOne('App\Models\Sales\SalesInvoice', 'delivery_note_id')->whereNotNull('number');
    }
    public function shipping_instruction()
    {
        return $this->belongsTo('App\Models\Shipping\ShippingInstruction', 'shipping_instruction_id');
    }
    public function job_order()
    {
        return $this->belongsTo('App\Models\Production\JobOrder', 'job_order_id');
    }
    public function address()
    {
        return $this->belongsTo('App\Models\Master\Profile\ProfileAddress', 'address_id');
    }
    public function vendor()
    {
        return $this->belongsTo('App\User', 'vendor_id');
    }

    public function log_print ()
    {
      return $this->hasOne('App\Models\LogPrint', 'transaction_number', 'number')->where('transaction_code', \Config::get('transactions.delivery_note.code'));
    }
}
