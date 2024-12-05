<?php

namespace App\Models\Master\Material;

use Illuminate\Database\Eloquent\Model;

class Color extends Model
{
    protected $fillable = ['code', 'name', 'is_active'];

    public function scopeActive($query)
    {
      return $query->where('is_active', 1);
    }

    public function raw_materials ()
    {
      return $this->hasMany('App\Models\Master\Material\RawMaterial', 'color_id');
    }
    public function item_materials ()
    {
      return $this->hasMany('App\Models\Master\Item\ItemMaterial', 'color_id');
    }
}
