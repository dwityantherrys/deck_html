<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class ChangeFinanceCoa extends Migration
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
            $table->string("kode_akun")->nullable()->change();
            $table->string("pos")->nullable()->change();
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
            $table->string("kode_akun")->change();
            $table->string("pos")->change();
        });
    }
}
