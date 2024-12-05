<?php

namespace App\Models\Master\City;

use Illuminate\Database\Eloquent\Model;

class District extends Model
{
  protected $fillable = ['id', 'name', 'type', 'city_id'];

  public function city ()
  {
    return $this->belongsTo('App\Models\Master\City\City', 'city_id', 'id');
  }
}
