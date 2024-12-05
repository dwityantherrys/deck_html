<?php

namespace App\Models\Production;

use Illuminate\Database\Eloquent\Model;

class BomDetail extends Model
{    
    protected $fillable = [
      'bom_id',
      'material_id',
      'production_process',
      'quantity',
      'unit',
      'costing',
      'remark'
    ];

    public function bom ()
    {
      return $this->belongsTo('App\Models\Production\Bom', 'bom_id');
    }
    public function raw_material ()
    {
      return $this->belongsTo('App\Models\Master\Material\RawMaterial', 'material_id');
    }
}
