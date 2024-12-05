<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateDeliveryNotesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('delivery_notes', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('shipping_instruction_id');
            $table->date('date')->nullable()->comment('tanggal surat jalan');
            $table->string('number', 8)->nullable()->comment('delivery note / surat jalan');
            $table->smallInteger('status')->comment('0=waiting; 1=process; 2=finish; 3=retur')->default(0);
            $table->smallInteger('shipping_method_id')->comment('1=pickup,2=pickup_point,3=delivery')->default(1);
            $table->unsignedBigInteger('address_id')->nullable();
            $table->integer('shipping_cost')->default(0);
            $table->string('remark')->nullable()->comment('keterangan tambahan');
            $table->timestamps();
            $table->softDeletes();

            $table->foreign('shipping_instruction_id')->references('id')->on('shipping_instructions')->onDelete('restrict');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('delivery_notes');
    }
}
