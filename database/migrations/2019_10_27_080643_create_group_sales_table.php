<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateGroupSalesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('sales', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('quotation_number', 8);
            $table->datetime('quotation_date');
            $table->datetime('quotation_expiration_date')->nullable();
            $table->unsignedBigInteger('customer_id')->comment('user_id pembeli');
            $table->smallInteger('quotation_status')->comment('0=quotation; 1=accepted; 2=rejected/canceled')->default(0);
            $table->string('order_number', 8)->nullable();
            $table->datetime('order_date')->nullable();
            $table->smallInteger('order_status')->comment('0=pending; 1=process; 2=finish; 3=canceled')->default(0);
            $table->unsignedBigInteger('warehouse_id')->nullable()->comment('location out');
            $table->unsignedBigInteger('shipping_method_id')->nullable();
            $table->unsignedBigInteger('shipping_address_id')->nullable();
            $table->unsignedBigInteger('shipping_cost')->nullable();
            $table->integer('discount')->default(0);
            $table->integer('downpayment')->default(0);
            $table->integer('tax')->default(10);
            $table->integer('total_price')->default(0);
            $table->integer('grand_total_price')->default(0)->comment('result keseluruhan setelah perhitungan dp, discount, ppn, shipping_cost');
            $table->unsignedBigInteger('payment_method_id')->nullable();
            $table->unsignedBigInteger('payment_bank_channel_id')->nullable();
            $table->smallInteger('transaction_channel')->comment('0=web; 1=mobile')->default(0);
            $table->string('canceled_reason')->comment('alasan rejected/canceled')->nullable();
            $table->unsignedBigInteger('created_by')->comment('user_id employee / user_id customer. tergantung transaction channelnya');
            $table->timestamps();
            $table->softDeletes();

            $table->foreign('payment_method_id')->references('id')->on('payment_methods')->onDelete('restrict');
            $table->foreign('payment_bank_channel_id')->references('id')->on('payment_bank_channels')->onDelete('restrict');
            $table->foreign('customer_id')->references('id')->on('users')->onDelete('restrict');
            $table->foreign('warehouse_id')->references('id')->on('warehouses')->onDelete('restrict');
            $table->foreign('created_by')->references('id')->on('users')->onDelete('restrict');
        });

        Schema::create('sales_details', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('sales_id');
            $table->smallInteger('quotation_status')->comment('0=quotation; 1=accepted; 2=rejected/canceled')->default(0);
            $table->smallInteger('order_status')->comment('0=pending; 1=process; 2=finish; 3=canceled')->default(0);
            $table->unsignedBigInteger('item_material_id');
            $table->decimal('width', 6, 2)->default(1);
            $table->decimal('height', 6, 2)->default(1);
            $table->decimal('length', 6, 2)->default(1);
            $table->decimal('weight')->default(1);
            $table->integer('sheet')->default(0);
            $table->decimal('quantity')->default(0)->comment('sheet x length');
            $table->boolean('is_custom_length')->default(false);
            $table->integer('discount')->default(0);
            $table->integer('tax')->default(0);
            $table->integer('price')->default(0);
            $table->integer('total_price')->default(0);
            $table->string('summary')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->foreign('sales_id')->references('id')->on('sales')->onDelete('restrict');
            $table->foreign('item_material_id')->references('id')->on('item_materials')->onDelete('restrict');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('sales_details');
        Schema::dropIfExists('sales');
    }
}
