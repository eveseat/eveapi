<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateCharacterPlanetRoutesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {

        Schema::create('character_planet_routes', function (Blueprint $table) {

            $table->bigInteger('character_id');
            $table->integer('planet_id');
            $table->bigInteger('route_id');
            $table->bigInteger('source_pin_id');
            $table->bigInteger('destination_pin_id');
            $table->integer('content_type_id');
            $table->float('quantity');

            $table->primary(['character_id', 'planet_id', 'route_id'], 'character_planet_routes_primary_key');
            $table->index('character_id');
            $table->index('planet_id');
            $table->index('route_id');

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

        Schema::dropIfExists('character_planet_routes');
    }
}
