<?php

namespace App\Models\Finance;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class COA extends Model
{
    //
    use SoftDeletes;

    protected $table = "finance_coa";
    protected $fillable = [
      "kode_akun",
      "nama_akun",
      "kode_akun_parent",
      "lk",
      "lk_kategori",
      "lk_pos",
      "pos",
      "saldo",
    ];

    public function parent() {
      if (isset($this->kode_akun_parent)) {
        return $this->where("kode_akun", $this->kode_akun_parent)->first()->nama_akun;
      }
      else {
        return null;
      }
    }

    public function children() {
      return $this->hasMany("App\Models\Finance\COA", "kode_akun_parent", "kode_akun");
    }
}
