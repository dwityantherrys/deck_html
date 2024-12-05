<?php

namespace App\Models\Master;

use Illuminate\Database\Eloquent\Model;

class Branch extends Model
{
    protected $fillable = ['name'];

    protected $table = 'branches';
    
}
