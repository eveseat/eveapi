<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateEveConquerableStationListsTable extends Migration
{

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {

        Schema::create('eve_conquerable_station_lists', function (Blueprint $table) {

            $table->integer('stationID')->unique();
            $table->string('stationName');
            $table->integer('stationTypeID');
            $table->integer('solarSystemID');
            $table->integer('corporationID');
            $table->string('corporationName');

            // Index
            $table->primary('stationID');

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

        Schema::drop('eve_conquerable_station_lists');
    }
}
