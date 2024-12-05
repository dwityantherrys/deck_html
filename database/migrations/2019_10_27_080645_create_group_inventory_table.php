<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateGroupInventoryTable extends Migration
{
  /**
  * Run the migrations.
  *
  * @return void
  */
  public function up()
  {
    Schema::create('inventories', function (Blueprint $table) {
      $table->bigIncrements('id');
      $table->smallInteger('type_inventory')->comment('0=raw_material, 1=item_material')->default(0);
      $table->unsignedBigInteger('reference_id')->comment('raw_material_id / item_material_id. dilihat dari type_inventorynya');
      $table->integer('cost_of_good')->default(0);
      $table->integer('stock')->default(0);
      $table->timestamps();
    });

    Schema::create('inventory_warehouses', function (Blueprint $table) {
      $table->bigIncrements('id');
      $table->string('inventory_warehouse_number')->nullable();
      $table->unsignedBigInteger('inventory_id');
      $table->unsignedBigInteger('warehouse_id');
      $table->unsignedBigInteger('receipt_detail_id')->nullable();
      $table->integer('selling_price')->default(0);
      $table->integer('stock')->default(0);
      $table->timestamps();

      $table->foreign('receipt_detail_id')->references('id')->on('purchase_receive_details')->onDelete('restrict');
      $table->foreign('inventory_id')->references('id')->on('inventories')->onDelete('restrict');
      $table->foreign('warehouse_id')->references('id')->on('warehouses')->onDelete('restrict');
    });

    Schema::create('inventory_movements', function (Blueprint $table) {
      $table->bigIncrements('id');
      $table->string('number');
      $table->smallInteger('type_movement')->comment('0=purchase_receipt, 1=prod. sales, 2=prod. stock')->default(0);
      $table->unsignedBigInteger('inventory_id');
      $table->unsignedBigInteger('inventory_warehouse_number')->nullable();
      $table->integer('quantity')->default(0);
      $table->unsignedBigInteger('warehouse_departure_id')->nullable()->comment('warehouse asal');
      $table->unsignedBigInteger('warehouse_arrival_id')->comment('warehouse target');
      $table->datetime('date_departure');
      $table->datetime('date_arrival')->nullable();
      $table->smallInteger('status')->comment('0=process, 1=finish, 2=cancel')->default(0);
      $table->boolean('is_defect')->default(false)->comment('0=not_defect, 1=defect');
      $table->timestamps();

      $table->foreign('inventory_id')->references('id')->on('inventories')->onDelete('restrict');
      $table->foreign('warehouse_departure_id')->references('id')->on('warehouses')->onDelete('restrict');
      $table->foreign('warehouse_arrival_id')->references('id')->on('warehouses')->onDelete('restrict');
    });
  }

  /**
  * Reverse the migrations.
  *
  * @return void
  */
  public function down()
  {
    Schema::disableForeignKeyConstraints();
    Schema::dropIfExists('inventory_movements');
    Schema::dropIfExists('inventory_warehouses');
    Schema::dropIfExists('inventories');
    Schema::enableForeignKeyConstraints();
  }
}
