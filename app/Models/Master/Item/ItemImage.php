<?php

namespace App\Models\Master\Item;

use Illuminate\Database\Eloquent\Model;

class ItemImage extends Model
{
    protected $fillable = ['image', 'item_id', 'is_thumbnail', 'is_active'];
    protected $appends = ['image_url'];

    public function getImageUrlAttribute ()
    {
      $source = !empty($this->attributes['image']) ? "/storage/" . $this->attributes['image'] : "/img/no-image.png";
      return asset($source);
    }

    public function item ()
    {
      return $this->belongsTo('App\Models\Master\Item\Item', 'item_id');
    }
}
