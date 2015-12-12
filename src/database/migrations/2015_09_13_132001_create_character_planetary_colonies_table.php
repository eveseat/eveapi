<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class CreateCharacterPlanetaryColoniesTable extends Migration
{

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {

        Schema::create('character_planetary_colonies', function (Blueprint $table) {

            $table->increments('id');

            $table->integer('solarSystemID');
            $table->string('solarSystemName');
            $table->integer('planetID');
            $table->string('planetName');
            $table->integer('planetTypeID');
            $table->string('planetTypeName');
            $table->integer('ownerID');
            $table->string('ownerName');
            $table->dateTime('lastUpdate');
            $table->integer('upgradeLevel');
            $table->integer('numberOfPins');

            // Indexes
            $table->index('ownerID');
            $table->index('solarSystemID');
            $table->index('planetID');

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

        Schema::drop('character_planetary_colonies');
    }
}
