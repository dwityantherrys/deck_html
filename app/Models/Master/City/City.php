<?php

namespace App\Models\Master\City;

use Illuminate\Database\Eloquent\Model;

class City extends Model
{
  protected $fillable = ['id', 'name', 'type', 'postal_code', 'province_id'];

  public function province ()
  {
    return $this->belongsTo('App\Models\Master\City\Province', 'province_id', 'id');
  }
}
