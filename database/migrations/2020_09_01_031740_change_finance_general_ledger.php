<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class ChangeFinanceGeneralLedger extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('finance_general_ledger', function (Blueprint $table) {
            //
            $table->string("model")->after("nominal")->nullable();
            $table->string("ref")->after("model")->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('finance_general_ledger', function (Blueprint $table) {
            //
            $table->dropColumn(["model", "ref"]);
        });
    }
}
