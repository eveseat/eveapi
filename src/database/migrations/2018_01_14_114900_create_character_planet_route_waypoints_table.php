<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateCharacterPlanetRouteWaypointsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {

        Schema::create('character_planet_route_waypoints', function (Blueprint $table) {

            $table->bigInteger('character_id');
	        $table->integer('planet_id');
	        $table->bigInteger('route_id');
	        $table->bigInteger('pin_id');

            $table->primary(['character_id', 'planet_id', 'route_id', 'pin_id'], 'character_planet_route_waypoints_primary_key');
            $table->index('character_id');
            $table->index('planet_id');
	        $table->index('route_id');
	        $table->index('pin_id');

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

        Schema::dropIfExists('character_planet_route_waypoints');
    }
}
