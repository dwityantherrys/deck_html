<?php

namespace App\Models\Master;

use Illuminate\Database\Eloquent\Model;

class BannerMobile extends Model
{
    protected $table = 'banners';
    protected $fillable = ['name', 'description', 'image', 'is_active'];
    protected $appends = ['image_url'];

    /** additional attribut, after make function, add to $appends variable */
    public function getImageUrlAttribute ()
    {
        $source = !empty($this->attributes['image']) ? "/storage/" . $this->attributes['image'] : "/img/no-image.png";
        return asset($source);
    }

    /** scope */
    public function scopeActive($query)
    {
      return $query->where('is_active', 1);
    }
}
