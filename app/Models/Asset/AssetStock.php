<?php

namespace App\Models\Asset;

use Illuminate\Database\Eloquent\Model;

class AssetStock extends Model
{
    protected $fillable = ['stock', 'asset_id', 'warehouse_id'];

    public function asset ()
    {
      return $this->belongsTo('App\Models\Master\Asset\Asset', 'asset_id', 'id');
    }

    public function warehouse ()
    {
      return $this->belongsTo('App\Models\Master\Warehouse', 'warehouse_id', 'id');
    }
}
