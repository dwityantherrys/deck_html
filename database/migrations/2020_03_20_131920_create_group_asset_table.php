<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateGroupAssetTable extends Migration
{
  /**
  * Run the migrations.
  *
  * @return void
  */
  public function up()
  {
    Schema::create('asset_brands', function (Blueprint $table) {
      $table->bigIncrements('id');
      $table->string('name');
      $table->boolean('is_active')->default(true);
      $table->timestamps();
    });

    Schema::create('asset_categories', function (Blueprint $table) {
      $table->bigIncrements('id');
      $table->string('name');
      $table->string('description')->nullable();
      $table->boolean('is_active')->default(true);
      $table->timestamps();
    });

    Schema::create('assets', function (Blueprint $table) {
      $table->bigIncrements('id');
      $table->string('code');
      $table->string('name');
      $table->string('image')->nullable();
      $table->string('description')->nullable();
      $table->unsignedBigInteger('asset_category_id');
      $table->unsignedBigInteger('asset_brand_id');
      $table->boolean('is_active')->default(true);
      $table->timestamps();

      $table->foreign('asset_category_id')->references('id')->on('asset_categories')->onDelete('restrict');
      $table->foreign('asset_brand_id')->references('id')->on('asset_brands')->onDelete('restrict');
    });

    Schema::create('asset_stocks', function (Blueprint $table) {
      $table->bigIncrements('id');
      $table->string('stock');
      $table->unsignedBigInteger('asset_id');
      $table->unsignedBigInteger('warehouse_id');
      $table->timestamps();

      $table->foreign('asset_id')->references('id')->on('assets')->onDelete('restrict');
      $table->foreign('warehouse_id')->references('id')->on('warehouses')->onDelete('restrict');
    });

    Schema::create('asset_loans', function (Blueprint $table) {
      $table->bigIncrements('id');
      $table->string('loan_number', 8);
      $table->datetime('loan_date');
      $table->datetime('loan_expiration_date')->nullable();
      $table->unsignedBigInteger('customer_id')->comment('user_id pembeli');
      $table->smallInteger('loan_status')->comment('0=request; 1=accepted; 2=rejected/canceled')->default(0);
      $table->string('loan_canceled_reason')->comment('alasan rejected/canceled')->nullable();
      $table->unsignedBigInteger('warehouse_id')->nullable()->comment('location out');
      $table->unsignedBigInteger('created_by')->comment('user_id employee / user_id customer. tergantung transaction channelnya');
      $table->timestamps();
      $table->softDeletes();

      $table->foreign('customer_id')->references('id')->on('users')->onDelete('restrict');
      $table->foreign('warehouse_id')->references('id')->on('warehouses')->onDelete('restrict');
      $table->foreign('created_by')->references('id')->on('users')->onDelete('restrict');
    });

    Schema::create('asset_loan_details', function (Blueprint $table) {
      $table->bigIncrements('id');
      $table->unsignedBigInteger('asset_loan_id');
      $table->smallInteger('loan_status')->comment('0=request; 1=accepted; 2=rejected/canceled')->default(0);
      $table->unsignedBigInteger('asset_stock_id');
      $table->integer('quantity')->default(0);
      $table->timestamps();
      $table->softDeletes();

      $table->foreign('asset_loan_id')->references('id')->on('asset_loans')->onDelete('restrict');
      $table->foreign('asset_stock_id')->references('id')->on('asset_stocks')->onDelete('restrict');
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
    Schema::dropIfExists('asset_brands');
    Schema::dropIfExists('asset_categories');
    Schema::dropIfExists('assets');
    Schema::dropIfExists('asset_stocks');
    Schema::dropIfExists('asset_loans');
    Schema::dropIfExists('asset_loan_details');
    Schema::enableForeignKeyConstraints();
  }
}
