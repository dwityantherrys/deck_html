<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddGradeToItem extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('items', function (Blueprint $table) {
            //
            $table->string("item_az")->after("has_length_options")->nullable();
            $table->string("item_grade")->after("item_az")->nullable();
            $table->integer("min_stock")->after("item_grade")->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('items', function (Blueprint $table) {
            //
            $table->dropColumn("item_az");
            $table->dropColumn("item_grade");
            $table->dropColumn("min_stock");
        });
    }
}
