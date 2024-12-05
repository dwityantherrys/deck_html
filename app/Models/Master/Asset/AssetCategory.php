<?php

namespace App\Models\Master\Asset;

use Illuminate\Database\Eloquent\Model;

class AssetCategory extends Model
{
    protected $fillable = ['name', 'description', 'is_active'];

    public function assets ()
    {
        return $this->hasMany('App\Models\Master\Asset\Asset', 'asset_category_id')->active();
    }
}
