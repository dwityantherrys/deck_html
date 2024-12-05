<?php

namespace App\Models\Master;

use Illuminate\Database\Eloquent\Model;

class Company extends Model
{
    protected $fillable = ['name', 'address', 'phone', 'npwp', 'business_field', 'ceo_name', 'is_active'];

    public function profiles ()
    {
      return $this->hasMany('App\Models\Master\Profile\Profile', 'company_id');
    }
}
