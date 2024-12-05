<?php

namespace App\Models\Master\Asset;

use Illuminate\Database\Eloquent\Model;

class Asset extends Model
{
    protected $fillable = ['name', 'description', 'code', 'image', 'asset_category_id', 'asset_brand_id', 'is_active'];
    protected $appends = ['image_url'];

    public function getImageUrlAttribute ()
    {
        $source = !empty($this->attributes['image']) ? "/storage/" . $this->attributes['image'] : "/img/no-image.png";
        return asset($source);
    }

    public function category ()
    {
      return $this->belongsTo('App\Models\Master\Asset\AssetCategory', 'asset_category_id', 'id');
    }

    public function brand ()
    {
      return $this->belongsTo('App\Models\Master\Asset\AssetBrand', 'asset_brand_id', 'id');
    }
}
