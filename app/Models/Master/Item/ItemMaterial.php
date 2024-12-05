<?php

namespace App\Models\Master\Item;

use Illuminate\Database\Eloquent\Model;
use App\Models\Master\Warehouse;

class ItemMaterial extends Model
{
    public $regionActiveUser;

    protected $fillable = ['name', 'description', 'thick',  'weight', 'material_id', 'color_id', 'item_id', 'is_default', 'is_active'];

    /** scope */
    public function scopeActive($query)
    {
      return $query->where('is_active', 1);
    }

    /** relations */
    public function item ()
    {
      return $this->belongsTo('App\Models\Master\Item\Item', 'item_id');
    }
    public function material ()
    {
      return $this->belongsTo('App\Models\Master\Material\Material', 'material_id');
    }
    public function color ()
    {
      return $this->belongsTo('App\Models\Master\Material\Color', 'color_id');
    }
}
