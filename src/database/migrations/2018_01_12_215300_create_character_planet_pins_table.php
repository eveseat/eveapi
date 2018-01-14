<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateCharacterPlanetPinsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {

        Schema::create('character_planet_pins', function (Blueprint $table) {

            $table->bigInteger('character_id');
            $table->integer('planet_id');
            $table->bigInteger('pin_id');
            $table->integer('type_id');
            $table->integer('schematic_id')->nullable();
            $table->float('latitude');
            $table->float('longitude');
            $table->dateTimeTz('install_time')->nullable();
            $table->dateTimeTz('expiry_time')->nullable();
            $table->dateTimeTz('last_cycle_start')->nullable();

            $table->primary(['character_id', 'planet_id', 'pin_id']);
            $table->index('character_id');
            $table->index('planet_id');

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

        Schema::dropIfExists('character_planet_pins');
    }
}
