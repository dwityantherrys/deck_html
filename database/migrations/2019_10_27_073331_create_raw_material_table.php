<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateRawMaterialTable extends Migration
{
  /**
  * Run the migrations.
  *
  * @return void
  */
  public function up()
  {
    Schema::create('raw_materials', function (Blueprint $table) {
      $table->bigIncrements('id');
      $table->string('number');
      $table->string('name');
      $table->string('specification')->nullable();
      $table->decimal('thick', 10, 2)->default(0);
      $table->unsignedBigInteger('material_id');
      $table->unsignedBigInteger('color_id');
      $table->boolean('is_active')->default(true);
      $table->timestamps();

      $table->foreign('material_id')->references('id')->on('materials')->onDelete('restrict');
      $table->foreign('color_id')->references('id')->on('colors')->onDelete('restrict');
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
    Schema::dropIfExists('raw_materials');
    Schema::enableForeignKeyConstraints();
  }
}
