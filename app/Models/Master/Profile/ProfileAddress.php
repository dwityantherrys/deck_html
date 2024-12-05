<?php

namespace App\Models\Master\Profile;

use Illuminate\Database\Eloquent\Model;

class ProfileAddress extends Model
{
    const REGION_TYPE_CITY = 0;
    const REGION_TYPE_DISTRICT = 1;

    protected $fillable = [
      'address',
      'longtitude',
      'latitude',
      'region_type', 
      'region_id', 
      'is_default', 
      'is_billing_address', 
      'profile_id',
      'is_active'
    ];
    
    /** relations */
    public function profile ()
    {
      return $this->belongsTo('App\Models\Master\Profile\Profile', 'profile_id');
    }
    public function region_city ()
    {
      return $this->belongsTo('App\Models\Master\City\City', 'region_id');
    }
    public function region_district ()
    {
      return $this->belongsTo('App\Models\Master\City\District', 'region_id');
    }
}
