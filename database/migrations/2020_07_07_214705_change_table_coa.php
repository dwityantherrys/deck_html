<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class ChangeTableCoa extends Migration
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
            $table->dropColumn("kategori");
            $table->string("kode_akun_parent")->after("nama_akun")->nullable();
            $table->string("saldo")->after("kode_akun_parent")->nullable();
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
            $table->string("kategori");
            $table->dropColumn("kode_akun_parent");
            $table->dropColumn("saldo");
        });
    }
}
