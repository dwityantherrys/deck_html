<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateMasterTable extends Migration
{
  /**
  * Run the migrations.
  *
  * @return void
  */
  public function up()
  {
    Schema::create('colors', function (Blueprint $table) {
      $table->bigIncrements('id');
      $table->string('code');
      $table->string('name');
      $table->boolean('is_active')->default(true);
      $table->timestamps();
    });

    Schema::create('materials', function (Blueprint $table) {
      $table->bigIncrements('id');
      $table->string('name');
      $table->string('description')->nullable();
      $table->boolean('is_active')->default(true);
      $table->timestamps();
    });

    Schema::create('production_processes', function (Blueprint $table) {
      $table->bigIncrements('id');
      $table->string('name');
      $table->string('description')->nullable();
      $table->boolean('is_active')->default(true);
      $table->timestamps();
    });

    Schema::create('warehouses', function (Blueprint $table) {
      $table->bigIncrements('id');
      $table->string('name');
      $table->string('address');
      $table->smallInteger('region_type')->comment('0=city, 1=district')->nullable();
      $table->unsignedBigInteger('region_id')->comment('city_id / district_id tergantung region typenya')->nullable();
      $table->integer('type')->comment('0=inventory, 1=fabric')->default(0);
      $table->boolean('is_active')->default(true);
      $table->timestamps();
    });

    Schema::create('companies', function (Blueprint $table) {
      $table->bigIncrements('id');
      $table->string('name');
      $table->string('address')->nullable();
      $table->string('phone')->nullable();
      $table->string('npwp')->nullable();
      $table->string('business_field')->nullable();
      $table->string('ceo_name')->nullable();
      $table->boolean('is_active')->default(true);
      $table->timestamps();
    });

    Schema::create('payment_methods', function (Blueprint $table) {
      $table->bigIncrements('id');
      $table->string('code')->nullable()->comment('va prefix');
      $table->string('name');
      $table->string('image')->nullable();
      $table->string('rekening_number')->nullable();
      $table->string('channel')->nullable();
      $table->boolean('has_code_rule')->comment('punya aturan kode va atau tidak. 0=tidak, 1=ya')->default(false);
      $table->text('guide')->nullable();
      $table->smallInteger('available_at')->comment('0=offline in store, 1=online with app, 2=both')->default(0);
      $table->boolean('is_active')->default(true);
      $table->timestamps();
    });

    Schema::create('payment_bank_channels', function (Blueprint $table) {
      $table->bigIncrements('id');
      $table->string('name');
      $table->string('rekening_name')->nullable();
      $table->string('rekening_number')->nullable();
      $table->boolean('is_active')->default(true);
      $table->timestamps();
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
    Schema::dropIfExists('colors');
    Schema::dropIfExists('materials');
    Schema::dropIfExists('production_processes');
    Schema::dropIfExists('warehouses');
    Schema::dropIfExists('companies');
    Schema::dropIfExists('payment_methods');
    Schema::dropIfExists('payment_bank_channels');
    Schema::enableForeignKeyConstraints();
  }
}
