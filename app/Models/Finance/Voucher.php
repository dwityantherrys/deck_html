<?php

namespace App\Models\Finance;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Voucher extends Model
{
    //
    use SoftDeletes;

    protected $table = "finance_vouchers";
    protected $fillable = [
      "journal_no_urut",
      "keterangan",
      "nama_pic",
      "tanggal_transaksi",
    ];

    public function journal() {
      return $this->hasMany("App\Models\Finance\FinanceJournal", "no_transaksi", "journal_no_urut");
    }
}
