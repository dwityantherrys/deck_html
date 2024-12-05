<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateGroupItemTable extends Migration
{
  /**
  * Run the migrations.
  *
  * @return void
  */
  public function up()
  {
    Schema::create('item_categories', function (Blueprint $table) {
      $table->bigIncrements('id');
      $table->string('name');
      $table->string('description')->nullable();
      $table->string('image')->nullable();
      $table->boolean('is_active')->default(true);
      $table->timestamps();
    });

    Schema::create('items', function (Blueprint $table) {
      $table->bigIncrements('id');
      $table->string('name');
      $table->string('description')->nullable();
      $table->integer('height')->default(0);
      $table->integer('width')->default(0);
      $table->decimal('length', 8)->default(0);
      $table->integer('max_custom_length')->default(0);
      $table->integer('charge_custom_length')->default(0);
      $table->boolean('has_length_options')->default(true);
      $table->unsignedBigInteger('item_category_id');
      $table->boolean('is_active')->default(true);
      $table->timestamps();

      $table->foreign('item_category_id')->references('id')->on('item_categories')->onDelete('restrict');
    });

    Schema::create('item_reviews', function (Blueprint $table) {
      $table->bigIncrements('id');
      $table->string('comment')->nullable();
      $table->integer('rate')->nullable();
      $table->unsignedBigInteger('item_id');
      $table->unsignedBigInteger('sales_detail_id')->nullable();
      $table->timestamps();

      $table->foreign('item_id')->references('id')->on('items')->onDelete('restrict');
    });

    Schema::create('item_images', function (Blueprint $table) {
      $table->bigIncrements('id');
      $table->string('image');
      $table->unsignedBigInteger('item_id');
      $table->boolean('is_thumbnail')->default(true);
      $table->boolean('is_active')->default(true);
      $table->timestamps();

      $table->foreign('item_id')->references('id')->on('items')->onDelete('restrict');
    });

    Schema::create('item_materials', function (Blueprint $table) {
      $table->bigIncrements('id');
      $table->string('name')->nullable();
      $table->string('description')->nullable();
      $table->decimal('thick', 8, 2)->default(0);
      $table->decimal('weight')->default(0);
      $table->unsignedBigInteger('color_id');
      $table->unsignedBigInteger('material_id');
      $table->unsignedBigInteger('item_id');
      $table->boolean('is_default')->default(true);
      $table->boolean('is_active')->default(true);
      $table->timestamps();

      $table->foreign('color_id')->references('id')->on('colors')->onDelete('restrict');
      $table->foreign('material_id')->references('id')->on('materials')->onDelete('restrict');
      $table->foreign('item_id')->references('id')->on('items')->onDelete('restrict');
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
    Schema::dropIfExists('item_materials');
    Schema::dropIfExists('item_images');
    Schema::dropIfExists('item_reviews');
    Schema::dropIfExists('items');
    Schema::dropIfExists('item_categories');
    Schema::enableForeignKeyConstraints();
  }
}
