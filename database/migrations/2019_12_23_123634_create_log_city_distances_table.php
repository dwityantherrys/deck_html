<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateLogCityDistancesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('log_city_distances', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('departure_id')->comment('city_id');
            $table->unsignedBigInteger('arrival_id')->comment('city_id');
            $table->decimal('distance_in_km');
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
        Schema::dropIfExists('log_city_distances');
    }
}
