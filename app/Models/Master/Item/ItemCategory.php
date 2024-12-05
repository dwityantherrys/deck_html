<?php

namespace App\Models\Master\Item;

use Illuminate\Database\Eloquent\Model;

class ItemCategory extends Model
{
    protected $fillable = ['name', 'description', 'image', 'is_active'];
    protected $appends = ['image_url'];

    public function getImageUrlAttribute ()
    {
        $source = !empty($this->attributes['image']) ? "/storage/" . $this->attributes['image'] : "/img/no-image.png";
        return asset($source);
    }

    public function items ()
    {
        return $this->hasMany('App\Models\Master\Item\Item', 'item_category_id')->active();
    }
}
