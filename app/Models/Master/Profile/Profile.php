<?php

namespace App\Models\Master\Profile;

use Illuminate\Database\Eloquent\Model;

class Profile extends Model
{
    protected $fillable = [
      'name',
      'image',
      'phone',
      'fax',
      'npwp_number',
      'identity_number',
      'identity_image',
      'bank_name',
      'bank_account',
      'company_id',
      'user_id',
      'is_active'
    ];
    
    protected $appends = ['identity_image_url', 'image_url'];

    public function getImageUrlAttribute ()
    {
        $source = !empty($this->attributes['image']) ? "/storage/" . $this->attributes['image'] : "/img/no-image.png";
        return asset($source);
    }
    public function getIdentityImageUrlAttribute ()
    {
        $source = !empty($this->attributes['identity_image']) ? "/storage/" . $this->attributes['identity_image'] : "/img/no-image.png";
        return asset($source);
    }

    public function user ()
    {
      return $this->belongsTo('App\User', 'user_id');
    }
    public function company ()
    {
      return $this->belongsTo('App\Models\Master\Company', 'company_id');
    }
    public function application_paylater ()
    {
      return $this->hasOne('App\Models\Master\Profile\ApplicationPaylater', 'profile_id');
    }
    public function transaction_setting ()
    {
      return $this->hasOne('App\Models\Master\Profile\ProfileTransactionSetting', 'profile_id');
    }
    public function addresses ()
    {
      return $this->hasMany('App\Models\Master\Profile\ProfileAddress', 'profile_id');
    }
    public function billing_address ()
    {
      return $this->hasOne('App\Models\Master\Profile\ProfileAddress', 'profile_id')->where('is_billing_address', true);
    }
    public function default_address ()
    {
      return $this->hasOne('App\Models\Master\Profile\ProfileAddress', 'profile_id')->where('is_default', true);
    }
}
