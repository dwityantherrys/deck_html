<?php

namespace App\Models\Master\Profile;

use Illuminate\Database\Eloquent\Model;

class ApplicationPaylater extends Model
{
    const APPLICATION_PENDING = 0;
    const APPLICATION_ACCEPT = 1;
    const APPLICATION_DECLINE = 2;
    const APPLICATION_EMPTY = 3; //belum daftar

    protected $fillable = ['profile_id', 'status', 'date_application', 'date_validation'];
    protected $dates = ['created_at', 'updated_at', 'date_application', 'date_validation'];
    protected $appends = ['status_in_text'];

    public function getStatusInTextAttribute ()
    {
        $statusInText = '';
        
        switch ($this->attributes['status']) {
            case self::APPLICATION_PENDING:
                $statusInText = 'pending';
                break;
            case self::APPLICATION_ACCEPT:
                $statusInText = 'disetujui';
                break;
            case self::APPLICATION_DECLINE:
                $statusInText = 'ditolak';
                break;
            
            default:
                $statusInText = 'belum mendaftar';
                break;
        }

        return $statusInText;
    }

    public function profile () {
        return $this->belongsTo('App\Models\Master\Profile\Profile', 'profile_id');
    }
}
