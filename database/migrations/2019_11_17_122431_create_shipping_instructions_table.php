<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateShippingInstructionsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('shipping_instructions', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('sales_id')->nullable();
            $table->unsignedBigInteger('good_receipt_id')->nullable();
            $table->date('date')->nullable()->comment('tanggal perintah muat');
            $table->string('number', 8)->nullable()->comment('nomor perintah muat');
            $table->smallInteger('status')->comment('0=waiting relesase; 1=release;')->default(0);
            $table->smallInteger('shipping_method_id')->comment('1=pickup,2=pickup_point,3=delivery')->default(1);
            $table->integer('shipping_cost')->default(0);
            $table->unsignedBigInteger('address_id')->nullable()->comment('alamat pengiriman bisa warehouse_id / address_id, tergantung shipping methodnya');
            $table->string('remark')->nullable()->comment('keterangan tambahan');
            $table->timestamps();
            $table->softDeletes();

            $table->foreign('sales_id')->references('id')->on('sales')->onDelete('restrict');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('shipping_instructions');
    }
}
