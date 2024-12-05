<?php

namespace App\Models\Master\Bom;

use Illuminate\Database\Eloquent\Model;

class ProductionProcess extends Model
{
    protected $fillable = ['name', 'description', 'is_active'];
}
