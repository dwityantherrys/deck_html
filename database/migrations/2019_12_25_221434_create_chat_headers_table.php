<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateChatHeadersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('chat_headers', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('chat_type_id');
            $table->unsignedBigInteger('customer_id');
            $table->unsignedBigInteger('employee_id')->nullable();
            $table->unsignedBigInteger('refference_id')->nullable()->comment('bisa sales_id / item_id');
            $table->timestamps();

            $table->foreign('employee_id')->references('id')->on('users')->onDelete('restrict');
            $table->foreign('customer_id')->references('id')->on('users')->onDelete('restrict');
            $table->foreign('chat_type_id')->references('id')->on('chat_types')->onDelete('restrict');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('chat_headers');
    }
}
