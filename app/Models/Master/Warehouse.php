<?php

namespace App\Models\Master;

use Illuminate\Database\Eloquent\Model;

class Warehouse extends Model
{
    const REGION_TYPE_CITY = 0;
    const REGION_TYPE_DISTRICT = 1;

    const WAREHOUSE_TYPE_INVENTORY = 0;
    const WAREHOUSE_TYPE_FACTORY = 1;

    protected $fillable = ['name', 'address', 'type', 'region_type', 'region_id', 'is_active'];

    /** relations */
    public function region_city ()
    {
        return $this->belongsTo('App\Models\Master\City\City', 'region_id');
    }
    public function region_district ()
    {
        return $this->belongsTo('App\Models\Master\City\District', 'region_id');
    }
}
