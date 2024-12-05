<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateGroupProfileTable extends Migration
{
  /**
  * Run the migrations.
  *
  * @return void
  */
  public function up()
  {
    Schema::create('profiles', function (Blueprint $table) {
      $table->bigIncrements('id');
      $table->string('name');
      $table->string('image')->nullable();
      $table->string('phone')->nullable();
      $table->string('fax')->nullable();
      $table->string('npwp_number')->comment('nomor npwp')->nullable();
      $table->string('identity_number')->comment('nomor ktp')->nullable();
      $table->string('identity_image')->nullable();
      $table->unsignedBigInteger('company_id')->nullable();
      $table->unsignedBigInteger('user_id');
      $table->boolean('is_active')->default(true);
      $table->timestamps();

      $table->foreign('company_id')->references('id')->on('companies')->onDelete('restrict');
      $table->foreign('user_id')->references('id')->on('users')->onDelete('restrict');
    });

    Schema::create('profile_addresses', function (Blueprint $table) {
      $table->bigIncrements('id');
      $table->string('address');
      $table->string('longtitude')->nullable();
      $table->string('latitude')->nullable();
      $table->smallInteger('region_type')->comment('0=city, 1=district')->nullable();
      $table->unsignedBigInteger('region_id')->comment('city_id / district_id tergantung region typenya')->nullable();
      $table->boolean('is_default')->default(true);
      $table->boolean('is_billing_address')->default(true);
      $table->unsignedBigInteger('profile_id');
      $table->boolean('is_active')->default(true);
      $table->timestamps();

      $table->foreign('profile_id')->references('id')->on('profiles')->onDelete('restrict');
    });

    Schema::create('profile_transaction_settings', function (Blueprint $table) {
      $table->bigIncrements('id');
      $table->integer('limit')->nullable();
      $table->string('tempo_type', 12)->comment('lihat const model profile_transaction_settings')->default('NOT_USED');
      $table->integer('tempo_charge_day')->comment('date/day tergantung tempo_type')->nullable();
      $table->integer('tempo_charge_month')->nullable();
      $table->integer('tempo_charge_week')->nullable();
      $table->integer('markdown_sales')->nullable();
      $table->integer('markdown_purchase')->nullable();
      $table->boolean('is_allowed_paylater')->default(false);
      $table->boolean('is_allowed_installment')->default(false)->comment('cicilan');
      $table->integer('minimum_downpayment')->nullable()->comment('dp dalam %');
      $table->unsignedBigInteger('payment_method_id');
      $table->unsignedBigInteger('profile_id');
      $table->unsignedBigInteger('created_by');
      $table->timestamps();

      $table->foreign('payment_method_id')->references('id')->on('payment_methods')->onDelete('restrict');
      $table->foreign('created_by')->references('id')->on('users')->onDelete('restrict');
      $table->foreign('profile_id')->references('id')->on('profiles')->onDelete('restrict');
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
    Schema::dropIfExists('profiles');
    Schema::dropIfExists('profile_addresses');
    Schema::dropIfExists('profile_transaction_settings');
    Schema::enableForeignKeyConstraints();
  }
}
