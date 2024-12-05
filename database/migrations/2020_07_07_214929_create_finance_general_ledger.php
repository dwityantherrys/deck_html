<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateFinanceGeneralLedger extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('finance_general_ledger', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string("no_transaksi");
            $table->string("kode_akun");
            $table->string("pos");
            $table->integer("nominal");
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('finance_general_ledger');
    }
}
