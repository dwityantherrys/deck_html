<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class ChangePaymentBankChannel extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('payment_bank_channels', function (Blueprint $table) {
            //
            $table->string("kode_akun")->after("rekening_number")->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('payment_bank_channels', function (Blueprint $table) {
            //
            $table->dropColumn("kode_akun");
        });
    }
}
