<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class CreateCharacterPlanetaryRoutesTable extends Migration
{

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {

        Schema::create('character_planetary_routes', function (Blueprint $table) {

            $table->increments('id');

            $table->bigInteger('routeID');
            $table->integer('ownerID');
            $table->integer('planetID');
            $table->bigInteger('sourcePinID');
            $table->bigInteger('destinationPinID');
            $table->integer('contentTypeID');
            $table->string('contentTypeName');
            $table->integer('quantity');
            $table->bigInteger('waypoint1');
            $table->bigInteger('waypoint2');
            $table->bigInteger('waypoint3');
            $table->bigInteger('waypoint4');
            $table->bigInteger('waypoint5');

            // Indexes
            $table->index('routeID');
            $table->index('ownerID');
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

        Schema::drop('character_planetary_routes');
    }
}
