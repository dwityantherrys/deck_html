<?php

namespace App\Models\Master\Material;

use Illuminate\Database\Eloquent\Model;

class Material extends Model
{
    protected $fillable = ['name', 'description', 'is_active'];

    public function scopeActive($query)
    {
      return $query->where('is_active', 1);
    }

    public function raw_materials ()
    {
      return $this->hasMany('App\Models\Master\Material\RawMaterial', 'material_id');
    }
    public function item_materials ()
    {
      return $this->hasMany('App\Models\Master\Item\ItemMaterial', 'material_id');
    }
}
