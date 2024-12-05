<?php

namespace App\Models\Master\Item;

use Illuminate\Database\Eloquent\Model;

class ItemLength extends Model
{
    protected $fillable = ['name', 'length', 'is_active'];
    
    public function scopeActive($query)
    {
      return $query->where('is_active', 1);
    }
}
