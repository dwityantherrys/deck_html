<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class RoleMenu extends Model
{
    protected $fillable = ['menu_key', 'role_id'];

    public function role ()
    {
        return $this->belongsTo('App\Models\Role', 'role_id');
    }
}
