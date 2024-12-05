<?php

namespace App;

use Illuminate\Notifications\Notifiable;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Foundation\Auth\User as Authenticatable;

class User extends Authenticatable
{
    use Notifiable;

    const REGION_TYPE_CITY = 0;
    const REGION_TYPE_DISTRICT = 1;

    protected $guarded = [];

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = [ 'password', 'remember_token' ];

    /**
     * The attributes that should be cast to native types.
     *
     * @var array
     */
    protected $casts = [
        'email_verified_at' => 'datetime'
    ];

    /** relations */
    public function region_city ()
    {
      return $this->belongsTo('App\Models\Master\City\City', 'region_id');
    }
    public function region_district ()
    {
      return $this->belongsTo('App\Models\Master\City\District', 'region_id');
    }
    public function profile ()
    {
      return $this->hasOne('App\Models\Master\Profile\Profile', 'user_id');
    }
    public function role ()
    {
      return $this->belongsTo('App\Models\Role', 'role_id');
    }
}
