<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateSalesTransactionsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('sales_transactions', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('code');
            $table->unsignedBigInteger('account_receivable_id');
            $table->unsignedBigInteger('sales_invoice_id')->nullable();
            $table->integer('amount')->default(0);
            $table->string('snap_token')->nullable();
            $table->string('va_number')->nullable();
            $table->string('note')->nullable();
            $table->smallInteger('status')->comment('0=pending, 1=success, 2=failed, 3=expired');
            $table->datetime('paid_of_date')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->foreign('account_receivable_id')->references('id')->on('account_receivables')->onDelete('restrict');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('sales_transactions');
    }
}
