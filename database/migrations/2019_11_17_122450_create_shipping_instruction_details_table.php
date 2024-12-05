<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateShippingInstructionDetailsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('shipping_instruction_details', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('shipping_instruction_id');
            $table->unsignedBigInteger('sales_detail_id');
            $table->unsignedBigInteger('good_receipt_detail_id')->nullable();
            $table->decimal('quantity', 12, 2)->default(0);
            $table->timestamps();
            $table->softDeletes();

            $table->foreign('sales_detail_id')->references('id')->on('sales_details')->onDelete('restrict');
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
        Schema::dropIfExists('shipping_instruction_details');
    }
}
