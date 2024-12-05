<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateGroupProductionTable extends Migration
{
  /**
  * Run the migrations.
  *
  * @return void
  */
  public function up()
  {
    Schema::create('job_orders', function (Blueprint $table) {
      $table->bigIncrements('id');
      $table->string('number', 8);
      $table->datetime('date');
      $table->datetime('due_date')->nullable();
      $table->smallInteger('status')->comment('0=pending; 1=process; 2=finish sebagian; 3=finish; 4=canceled')->default(0);
      $table->smallInteger('type')->comment('0=sales; 1=production')->default(0);
      $table->unsignedBigInteger('sales_id')->nullable()->comment('sales order');
      $table->unsignedBigInteger('warehouse_id')->nullable()->comment('warehouse');
      $table->unsignedBigInteger('created_by')->comment('user_id employee / user_id customer. tergantung transaction channelnya');
      $table->timestamps();
      $table->softDeletes();

      $table->foreign('sales_id')->references('id')->on('sales')->onDelete('restrict');
      $table->foreign('warehouse_id')->references('id')->on('warehouses')->onDelete('restrict');
      $table->foreign('created_by')->references('id')->on('users')->onDelete('restrict');
    });

    Schema::create('job_order_details', function (Blueprint $table) {
      $table->bigIncrements('id');
      $table->unsignedBigInteger('job_order_id');
      $table->unsignedBigInteger('sales_detail_id')->nullable();
      $table->unsignedBigInteger('item_material_id')->nullable();
      $table->smallInteger('status')->comment('0=pending; 1=process; 2=finish sebagian; 3=finish; 4=canceled')->default(0);
      $table->integer('is_custom_length')->default(false);
      $table->decimal('length', 6, 2)->comment('length yang harus diproduksi')->nullable();
      $table->integer('sheet')->comment('sheet yang harus diproduksi')->nullable();
      $table->integer('quantity')->comment('quantity yang harus diproduksi');
      $table->integer('balance')->default(0)->comment('sisa quantity yang harus di produksi');
      $table->integer('balance_issued')->default(0)->comment('sisa quantity yang harus di issued');
      $table->timestamps();
      $table->softDeletes();

      $table->foreign('job_order_id')->references('id')->on('job_orders')->onDelete('restrict');
      $table->foreign('sales_detail_id')->references('id')->on('sales_details')->onDelete('restrict');
    });

    Schema::create('good_issueds', function (Blueprint $table) {
      $table->bigIncrements('id');
      $table->string('number', 8);
      $table->datetime('date');
      $table->smallInteger('status')->comment('0=pending; 1=process; 2=shipped sebagian; 3=shipped; 4=canceled')->default(0);
      $table->unsignedBigInteger('warehouse_id')->comment('warehouse untuk ambil raw materialnya');
      $table->unsignedBigInteger('factory_id')->comment('warehouse yang bertipe pabrik');
      $table->unsignedBigInteger('job_order_id')->comment('job order');
      $table->unsignedBigInteger('created_by')->comment('user_id employee / user_id customer. tergantung transaction channelnya');
      $table->timestamps();
      $table->softDeletes();

      $table->foreign('warehouse_id')->references('id')->on('warehouses')->onDelete('restrict');
      $table->foreign('factory_id')->references('id')->on('warehouses')->onDelete('restrict');
      $table->foreign('job_order_id')->references('id')->on('job_orders')->onDelete('restrict');
      $table->foreign('created_by')->references('id')->on('users')->onDelete('restrict');
    });

    Schema::create('good_issued_details', function (Blueprint $table) {
      $table->bigIncrements('id');
      $table->unsignedBigInteger('good_issued_id');
      $table->unsignedBigInteger('job_order_detail_id');
      $table->unsignedBigInteger('raw_material_id')->nullable();
      $table->unsignedBigInteger('inventory_warehouse_id');
      $table->integer('quantity')->comment('quantity yang harus diproduksi');
      $table->integer('balance')->default(0)->comment('sisa quantity yang harus di produksi');
      $table->smallInteger('status')->comment('0=pending; 1=process; 2=shipped sebagian; 3=shipped; 4=canceled')->default(0);
      $table->timestamps();
      $table->softDeletes();

      $table->foreign('good_issued_id')->references('id')->on('good_issueds')->onDelete('restrict');
      $table->foreign('job_order_detail_id')->references('id')->on('job_order_details')->onDelete('restrict');
      $table->foreign('inventory_warehouse_id')->references('id')->on('inventory_warehouses')->onDelete('restrict');
    });

    Schema::create('good_receipts', function (Blueprint $table) {
      $table->bigIncrements('id');
      $table->string('number', 8);
      $table->smallInteger('status')->comment('0=pending; 1=receipt;')->default(0);
      $table->datetime('date')->comment('tanggal barang diterima');
      $table->unsignedBigInteger('warehouse_id')->comment('warehouse pengirim raw materialnya');
      $table->unsignedBigInteger('factory_id')->comment('warehouse yang bertipe pabrik');
      $table->unsignedBigInteger('good_issued_id')->comment('good issued');
      $table->unsignedBigInteger('created_by')->comment('user_id employee / user_id customer. tergantung transaction channelnya');
      $table->timestamps();
      $table->softDeletes();

      $table->foreign('warehouse_id')->references('id')->on('warehouses')->onDelete('restrict');
      $table->foreign('factory_id')->references('id')->on('warehouses')->onDelete('restrict');
      $table->foreign('good_issued_id')->references('id')->on('good_issueds')->onDelete('restrict');
      $table->foreign('created_by')->references('id')->on('users')->onDelete('restrict');
    });

    Schema::create('good_receipt_details', function (Blueprint $table) {
      $table->bigIncrements('id');
      $table->unsignedBigInteger('good_receipt_id');
      $table->unsignedBigInteger('job_order_detail_id');
      $table->unsignedBigInteger('item_material_id');
      $table->unsignedBigInteger('inventory_warehouse_id')->nullable();
      $table->integer('quantity')->comment('quantity yang harus diproduksi');
      $table->boolean('is_defect')->comment('0=tidak defect; 1=defect')->default(false);
      $table->timestamps();
      $table->softDeletes();

      $table->foreign('good_receipt_id')->references('id')->on('good_receipts')->onDelete('restrict');
      $table->foreign('job_order_detail_id')->references('id')->on('job_order_details')->onDelete('restrict');
      $table->foreign('item_material_id')->references('id')->on('item_materials')->onDelete('restrict');
      $table->foreign('inventory_warehouse_id')->references('id')->on('inventory_warehouses')->onDelete('restrict');
    });

    Schema::create('good_receipt_consumes', function (Blueprint $table) {
      $table->bigIncrements('id');
      $table->unsignedBigInteger('good_receipt_detail_id');
      $table->unsignedBigInteger('good_issued_detail_id');
      $table->integer('quantity');
      $table->timestamps();
      $table->softDeletes();

      $table->foreign('good_receipt_detail_id')->references('id')->on('good_receipt_details')->onDelete('restrict');
      $table->foreign('good_issued_detail_id')->references('id')->on('good_issued_details')->onDelete('restrict');
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
    Schema::dropIfExists('job_orders');
    Schema::dropIfExists('job_order_details');
    Schema::dropIfExists('good_issueds');
    Schema::dropIfExists('good_issued_details');
    Schema::dropIfExists('good_receipts');
    Schema::dropIfExists('good_receipt_details');
    Schema::dropIfExists('good_receipt_consumes');
    Schema::enableForeignKeyConstraints();
  }
}
