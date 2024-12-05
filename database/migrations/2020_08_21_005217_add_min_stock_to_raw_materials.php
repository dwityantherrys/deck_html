<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddMinStockToRawMaterials extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('raw_materials', function (Blueprint $table) {
            //
            $table->string("raw_az")->after("color_id")->nullable();
            $table->string("raw_grade")->after("raw_az")->nullable();
            $table->integer("min_stock")->after("raw_grade")->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('raw_materials', function (Blueprint $table) {
            //
            $table->dropColumn("raw_az");
            $table->dropColumn("raw_grade");
            $table->dropColumn("min_stock");
        });
    }
}
