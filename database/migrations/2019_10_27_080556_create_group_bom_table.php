<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateGroupBomTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('boms', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('item_id')->comment('bisa raw_material, bisa item_material menyesuaikan production_Category');
            $table->smallInteger('production_category')->comment('0=raw_material, 1=finish_good, 2=semi finish');
            $table->integer('manufacture_quantity')->default(1);
            $table->integer('total_costing');
            $table->string('remark')->nullable();
            $table->timestamps();
            
            $table->foreign('item_id')->references('id')->on('item_materials')->onDelete('restrict');
        });
        
        Schema::create('bom_details', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('bom_id');
            $table->unsignedBigInteger('material_id');
            $table->string('production_process')->nullable();
            $table->integer('quantity')->default(0);
            $table->string('unit')->nullable();
            $table->integer('costing')->default(0);
            $table->string('remark')->nullable();
            $table->timestamps();

            $table->foreign('bom_id')->references('id')->on('boms')->onDelete('restrict');
            $table->foreign('material_id')->references('id')->on('raw_materials')->onDelete('restrict');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('bom_details');
        Schema::dropIfExists('boms');
    }
}
