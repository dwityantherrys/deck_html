<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddMaterialIdToPurchaseDetails extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('purchase_details', function (Blueprint $table) {
            //
            $table->unsignedBigInteger("item_material_id")->nullable()->after("raw_material_id");
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
        Schema::table('purchase_details', function (Blueprint $table) {
            //
            $table->dropForeign(["item_material_id"]);
            $table->dropColumn("item_material_id");
        });
    }
}
