<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateCharacterPlanetsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {

        Schema::create('character_planets', function (Blueprint $table) {

            $table->bigInteger('character_id');
            $table->integer('solar_system_id');
            $table->integer('planet_id');
            $table->integer('upgrade_level');
            $table->integer('num_pins');
            $table->dateTime('last_update');
            $table->enum('planet_type',
                ['temperate', 'barren', 'oceanic', 'ice', 'gas', 'lava', 'storm', 'plasma']);

            $table->primary(['character_id', 'planet_id']);
            $table->index('character_id');
            $table->index('solar_system_id');
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

        Schema::dropIfExists('character_planets');
    }
}
