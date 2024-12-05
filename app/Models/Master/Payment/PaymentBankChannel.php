<?php

namespace App\Models\Master\Payment;

use Illuminate\Database\Eloquent\Model;

class PaymentBankChannel extends Model
{
    protected $fillable = [ 'name', 'rekening_name', 'rekening_number', 'kode_akun', 'is_active' ];

    public function scopeActive($query)
    {
        return $query->where('is_active', 1);
    }

    public function coa() {
      return $this->belongsTo("App\Models\Finance\COA", "kode_akun", "kode_akun");
    }
}
