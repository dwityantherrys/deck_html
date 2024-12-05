<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateInventoryAdjustmentsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('inventory_adjustments', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('inventory_warehouse_id');
            $table->integer('stock_before_adjustment')->default(0);
            $table->integer('stock_after_adjustment')->default(0);
            $table->integer('cost_of_good_before_adjustment')->default(0);
            $table->integer('cost_of_good_after_adjustment')->default(0);
            $table->unsignedBigInteger('created_by');
            $table->timestamps();

            $table->foreign('inventory_warehouse_id')->references('id')->on('inventory_warehouses')->onDelete('restrict');
            $table->foreign('created_by')->references('id')->on('users')->onDelete('restrict');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('inventory_adjustments');
    }
}
