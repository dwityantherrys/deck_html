<?php

namespace App\Models\Production;

use Illuminate\Database\Eloquent\Model;

class Bom extends Model
{    
    const TYPE_CATEGORY_RAW = 0;
    const TYPE_CATEGORY_FINISH = 1;
    const TYPE_CATEGORY_SEMI = 2;

    protected $fillable = [
        'production_category',
        'item_id',
        'production_process',
        'manufacture_quantity',
        'total_costing',
        'created_at',
        'updated_at'
    ];

    public function bom_details ()
    {
      return $this->hasMany('App\Models\Production\BomDetail', 'bom_id');
    }
    public function item_material ()
    {
      return $this->belongsTo('App\Models\Master\Item\ItemMaterial', 'item_id');
    }
}
