<?php

namespace App\Models\Finance;

use Illuminate\Database\Eloquent\Model;

class FinanceJournal extends Model
{
    //
    protected $table = "finance_general_ledger";
    protected $fillable = [
      "no_transaksi",
      "kode_akun",
      "pos",
      "nominal",
      "model",
      "ref"
    ];

    public function coa() {
      return $this->belongsTo("App\Models\Finance\COA", "kode_akun", "kode_akun");
    }
}
