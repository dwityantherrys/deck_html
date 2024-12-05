<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateDeliveryNoteDetailsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('delivery_note_details', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('delivery_note_id');
            $table->unsignedBigInteger('shipping_instruction_detail_id');
            $table->decimal('quantity', 12, 2)->default(0);
            $table->smallInteger('status')->comment('0=wating; 1=process; 2=finish; 3=retur');
            $table->timestamps();
            $table->softDeletes();

            $table->foreign('shipping_instruction_detail_id')->references('id')->on('shipping_instruction_details')->onDelete('restrict');
            $table->foreign('delivery_note_id')->references('id')->on('delivery_notes')->onDelete('restrict');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('delivery_note_details');
    }
}
