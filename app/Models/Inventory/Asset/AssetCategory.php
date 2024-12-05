<?php

namespace App\Models\Inventory\Asset;

use Illuminate\Database\Eloquent\Model;

class AssetCategory extends Model
{
    protected $fillable = ['name', 'description', 'is_active'];

    public function assets ()
    {
        return $this->hasMany('App\Models\Inventory\Asset\Asset', 'asset_category_id')->active();
    }
}
