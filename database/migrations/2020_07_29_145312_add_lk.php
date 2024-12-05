<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddLk extends Migration
{
  /**
  * Run the migrations.
  *
  * @return void
  */
  public function up()
  {
    Schema::table('finance_coa', function (Blueprint $table) {
      //
      $table->string("lk")->after("kode_akun_parent")->nullable();
      $table->string("lk_kategori")->after("lk")->nullable();
      $table->string("lk_pos")->after("lk_kategori")->nullable();
    });
  }

  /**
  * Reverse the migrations.
  *
  * @return void
  */
  public function down()
  {
    Schema::table('finance_coa', function (Blueprint $table) {
      //
      $table->dropColumn(["lk", "lk_kategori", "lk_pos"]);
    });
  }
}
