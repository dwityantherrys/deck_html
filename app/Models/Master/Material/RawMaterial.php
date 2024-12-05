<?php

namespace App\Models\Master\Material;

use Illuminate\Database\Eloquent\Model;

class RawMaterial extends Model
{
    protected $fillable = ['number', 'name', 'specification', 'thick', 'material_id', 'min_stock', 'stock_planning','color_id', 'is_active'];

    public function color ()
    {
        return $this->belongsTo('App\Models\Master\Material\Color', 'color_id');
    }
    public function material ()
    {
        return $this->belongsTo('App\Models\Master\Material\Material', 'material_id');
    }
}
