<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateSalesInvoicesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('sales_invoices', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('delivery_note_id');
            $table->unsignedBigInteger('payment_method_id');
            $table->string('number')->nullable();
            $table->datetime('due_date')->nullable();
            $table->datetime('paid_of_date')->nullable();
            $table->smallInteger('status')->comment('0=billed; 1=paid off;');
            $table->decimal('total_quantity', 8, 2)->nullable();
            $table->integer('shipping_cost')->default(0);
            $table->integer('discount')->default(0);
            $table->integer('total_bill')->default(0);
            $table->integer('grand_total_bill')->default(0)->comment('perhitungan total dengan shipping cost, discount');
            $table->timestamps();
            $table->softDeletes();

            $table->foreign('payment_method_id')->references('id')->on('payment_methods')->onDelete('restrict');
            $table->foreign('delivery_note_id')->references('id')->on('delivery_notes')->onDelete('restrict');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('sales_invoices');
    }
}
