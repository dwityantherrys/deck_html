<?php

namespace App\Models\Master\City;

use Illuminate\Database\Eloquent\Model;

class Province extends Model
{
  protected $fillable = ['id', 'name'];

  public function cities ()
  {
    return $this->hasMany('App\Models\Master\City\City', 'province_id', 'id');
  }
}
