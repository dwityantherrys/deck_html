<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Role extends Model
{
    protected $fillable = ['name', 'display_name', 'description', 'parent_id', 'is_active'];

    public function scopeActive($query)
    {
        return $query->where('is_active', 1);
    }

    public function menus ()
    {
        return $this->hasMany('App\Models\RoleMenu', 'role_id');
    }
}
