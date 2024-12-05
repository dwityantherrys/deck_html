<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateTemplateBalanceSheetsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('template_balance_sheets', function (Blueprint $table) {
          $table->bigIncrements('id');
          $table->integer("posisi")->comment("1:aset;2:liabilitas");
          $table->text("pos");
          $table->text("akun_penambah")->nullable();
          $table->text("akun_pengurang")->nullable();
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
        Schema::dropIfExists('template_balance_sheets');
    }
}
