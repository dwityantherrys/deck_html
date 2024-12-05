<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateGroupVouchersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('vouchers', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('name');
            $table->string('code', 20);
            $table->integer('value_type')->default(0)->comment('0 = discount, 1 = cut price');
            $table->decimal('value');
            $table->integer('minimum_sales')->default(0);
            $table->smallInteger('limit_type')->comment('0=once, 1=multi daily, 2=multi weekly, 3=multi monthly')->nullable();
            $table->smallInteger('limit_usage')->nullable();
            $table->smallInteger('limit_customer')->nullable();
            $table->text('notes')->nullable();
            $table->datetime('start_date');
            $table->datetime('expiration_date')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        Schema::create('voucher_usages', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('voucher_id');
            $table->unsignedBigInteger('user_id');
            $table->unsignedBigInteger('sales_id')->nullable();
            $table->smallInteger('status_usage')->default(0)->comment('available, used, expired');
            $table->timestamps();

            $table->foreign('user_id')->references('id')->on('users');
            $table->foreign('voucher_id')->references('id')->on('vouchers')->onDelete('restrict');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('voucher_usages');
        Schema::dropIfExists('vouchers');
    }
}
