<?php

namespace App\Models\Master\Item;

use Illuminate\Database\Eloquent\Model;

class Item extends Model
{
    protected $table = 'item';
    protected $fillable = [
      'item_code',
      'name',
      'description',
      'purchase_price',
      'quantity',
      'unit_id',
      'item_vendor_id',
      'item_category_id',
      'jenis_pajak',
      'type',
      'is_active',
      'product_image'
    ];

    /** scoped */
    public function scopeActive($query)
    {
      return $query->where('is_active', 1);
    }

    public function item_category ()
    {
      return $this->belongsTo('App\Models\Master\Item\ItemCategory', 'item_category_id');
    }
    
    public function vendor ()
    {
      return $this->belongsTo('App\User', 'item_vendor_id');
    }

    public function unit ()
    {
      return $this->belongsTo('App\Models\Master\Unit', 'unit_id');
    }
}
