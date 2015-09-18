<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class CreateMapSovereigntiesTable extends Migration
{

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {

        Schema::create('map_sovereignties', function (Blueprint $table) {

            $table->integer('solarSystemID')->unique();
            $table->integer('allianceID');
            $table->integer('factionID');
            $table->string('solarSystemName');
            $table->integer('corporationID');

            // Indexes
            $table->primary('solarSystemID');
            $table->index('allianceID');
            $table->index('solarSystemName');

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

        Schema::drop('map_sovereignties');
    }
}
