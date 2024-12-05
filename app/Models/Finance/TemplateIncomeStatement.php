<?php

namespace App\Models\Finance;

use Illuminate\Database\Eloquent\Model;
use App\Models\Finance\COA;

class TemplateIncomeStatement extends Model
{
    //

    protected $fillable = [
      "pos",
      "akun_penambah",
      "akun_pengurang",
    ];
    protected $table = "template_income_statements";

    public function coa_penambah() {
      return COA::whereIn("id", explode(",", $this->akun_penambah))->get();
    }

    public function coa_pengurang() {
      return COA::whereIn("id", explode(",", $this->akun_pengurang))->get();
    }
}
