<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateLogPrintsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('log_prints', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('transaction_code', 6)->comment('bisa purchase, sales, good receive dll');
            $table->string('transaction_number')->comment('number dari purchase / sales / good receive dll');
            $table->unsignedBigInteger('employee_id')->comment('id user employee');
            $table->date('date');
            $table->timestamps();

            $table->foreign('employee_id')->references('id')->on('users')->onDelete('restrict');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('log_prints');
    }
}
