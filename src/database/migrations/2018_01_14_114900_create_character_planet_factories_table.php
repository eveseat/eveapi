<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateCharacterPlanetFactoriesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {

        Schema::create('character_planet_factories', function (Blueprint $table) {

            $table->bigInteger('character_id');
	        $table->integer('planet_id');
	        $table->bigInteger('pin_id');
	        $table->float('schematic_id');

            $table->primary(['character_id', 'planet_id', 'pin_id'], 'character_planet_factories_primary_key');
            $table->index('character_id');
            $table->index('planet_id');
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

        Schema::dropIfExists('character_planet_factories');
    }
}
