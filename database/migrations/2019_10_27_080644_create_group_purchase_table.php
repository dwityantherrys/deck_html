<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateGroupPurchaseTable extends Migration
{
  /**
  * Run the migrations.
  *
  * @return void
  */
  public function up()
  {
    Schema::create('purchases', function (Blueprint $table) {
      $table->bigIncrements('id');
      $table->datetime('request_date');
      $table->string('request_number', 8);
      $table->smallInteger('request_status')->comment('0=request, 1=accept, 2=reject')->default(0);
      $table->smallInteger('request_type')->comment('0=purchase, 1=sales')->default(0);
      $table->unsignedBigInteger('warehouse_id')->nullable()->comment('lokasi warehouse penerima');
      $table->unsignedBigInteger('sales_id')->nullable()->comment('sales_quotation');
      $table->datetime('order_date')->nullable();
      $table->datetime('order_due_date')->nullable();
      $table->string('order_number', 8)->nullable();
      $table->smallInteger('order_status')->comment('0=pending, 1=process, 2=finish, 3=cancel')->default(0);
      $table->unsignedBigInteger('vendor_id')->nullable();
      $table->integer('total_price');
      $table->unsignedBigInteger('request_by')->comment('user_id employee');
      $table->timestamps();
      $table->softDeletes();

      $table->foreign('sales_id')->references('id')->on('sales')->onDelete('restrict');
      $table->foreign('request_by')->references('id')->on('users')->onDelete('restrict');
    });

    Schema::create('purchase_details', function (Blueprint $table) {
      $table->bigIncrements('id');
      $table->smallInteger('request_status')->comment('0=request, 1=accept, 2=reject')->default(0);
      $table->smallInteger('order_status')->comment('0=pending, 1=process, 2=finish, 3=cancel')->default(0);
      $table->unsignedBigInteger('raw_material_id')->nullable();
      $table->decimal('quantity')->default(0);
      $table->integer('estimation_price');
      $table->integer('amount');
      $table->unsignedBigInteger('purchase_id')->nullable()->comment('sales_quotation');
      $table->timestamps();
      $table->softDeletes();

      $table->foreign('raw_material_id')->references('id')->on('raw_materials')->onDelete('restrict');
      $table->foreign('purchase_id')->references('id')->on('purchases')->onDelete('restrict');
    });

    Schema::create('purchase_receives', function (Blueprint $table) {
      $table->bigIncrements('id');
      $table->datetime('date')->nullable()->comment('receive date');
      $table->string('number', 8)->nullable()->comment('receive number');
      $table->smallInteger('status')->comment('0=terima sebagian, 1=terima semua')->default(1);
      $table->unsignedBigInteger('purchase_id')->comment('purchase order');
      $table->integer('discount')->default(0);
      $table->integer('total_price');
      $table->unsignedBigInteger('receive_by')->comment('user_id employee');
      $table->timestamps();
      $table->softDeletes();

      $table->foreign('purchase_id')->references('id')->on('purchases')->onDelete('restrict');
      $table->foreign('receive_by')->references('id')->on('users')->onDelete('restrict');
    });

    Schema::create('purchase_receive_details', function (Blueprint $table) {
      $table->bigIncrements('id');
      $table->unsignedBigInteger('purchase_detail_id')->nullable();
      $table->smallInteger('status')->comment('0=terima sebagian, 1=terima semua')->default(1);
      $table->unsignedBigInteger('purchase_receive_id');
      $table->decimal('quantity', 12, 2)->default(0);
      $table->integer('discount')->default(0);
      $table->integer('estimation_price');
      $table->integer('amount')->default(0)->comment('total quantity*estimation_price - discount');
      $table->timestamps();
      $table->softDeletes();

      $table->foreign('purchase_detail_id')->references('id')->on('purchase_details')->onDelete('restrict');
      $table->foreign('purchase_receive_id')->references('id')->on('purchase_receives')->onDelete('restrict');
    });

    Schema::create('purchase_invoices', function (Blueprint $table) {
      $table->bigIncrements('id');
      $table->datetime('date_of_issued')->nullable();
      $table->datetime('due_date')->nullable();
      $table->datetime('paid_date')->nullable();
      $table->string('number')->nullable();
      $table->integer('discount')->default(0);
      $table->decimal('downpayment', 12, 2)->nullable();
      $table->smallInteger('status')->comment('0=terima sebagian, 1=terima semua')->default(1);
      $table->unsignedBigInteger('purchase_receive_id')->comment('purchase order');
      $table->integer('bill')->default(0);
      $table->integer('balance')->default(0)->comment('bill - down payment');
      $table->timestamps();
      $table->softDeletes();

      $table->foreign('purchase_receive_id')->references('id')->on('purchase_receives')->onDelete('restrict');
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
    Schema::dropIfExists('purchases');
    Schema::dropIfExists('purchase_details');
    Schema::dropIfExists('purchase_receives');
    Schema::dropIfExists('purchase_receive_details');
    Schema::dropIfExists('purchase_invoices');
    Schema::enableForeignKeyConstraints();
  }
}
