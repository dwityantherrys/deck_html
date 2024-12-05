<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateVouchersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('finance_vouchers', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string("journal_no_urut");
            $table->text("keterangan");
            $table->text("nama_pic");
            $table->date("tanggal_transaksi");
            $table->softDeletes();
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
        Schema::dropIfExists('finance_vouchers');
    }
}
