<?php

namespace App\Models\Inventory;

use Illuminate\Database\Eloquent\Model;

class InventoryMovement extends Model
{
    const MOVEMENT_PROCESS = 0;
    const MOVEMENT_FINISH = 1;
    const MOVEMENT_CANCEL = 2;

    const TYPE_MOVEMENT_PURCHASE = 0;
    const TYPE_MOVEMENT_SALES = 1;
    const TYPE_MOVEMENT_PRODUCTION = 2;

    protected $fillable = [
        'inventory_id',
        'number',
        'quantity',
        'type_movement',
        'warehouse_departure_id',
        'warehouse_arrival_id',
        'date_departure',
        'date_arrival',
        'status',
        'is_detect',
    ];

    protected $dates = ['created_at', 'updated_at', 'date_departure', 'date_arrival'];
    protected $appends = ['date_departure_formated', 'date_arrival_formated'];

    public function getDateDepartureFormatedAttribute ()
    {
      return date('m/d/Y', strtotime($this->attributes['date_departure']));
    }
    public function getDateArrivalFormatedAttribute ()
    {
      return date('m/d/Y', strtotime($this->attributes['date_arrival']));
    }

    public function inventory ()
    {
        return $this->belongsTo('App\Models\Inventory\Inventory', 'inventory_id');
    }
    public function warehouse_departure ()
    {
        return $this->belongsTo('App\Models\Master\Warehouse', 'warehouse_departure_id');
    }
    public function warehouse_arrival ()
    {
        return $this->belongsTo('App\Models\Master\Warehouse', 'warehouse_arrival_id');
    }
}
