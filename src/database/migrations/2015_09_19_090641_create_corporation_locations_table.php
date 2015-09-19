<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class CreateCorporationLocationsTable extends Migration
{

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {

        Schema::create('corporation_locations', function (Blueprint $table) {

            $table->integer('corporationID');

            $table->bigInteger('itemID');
            $table->string('itemName');
            $table->double('x');
            $table->double('y');
            $table->double('z');
            $table->integer('mapID')->nullable();
            $table->string('mapName')->nullable();

            // Indexes
            $table->index('corporationID');
            $table->index('itemID');
            $table->index('mapID');

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

        Schema::drop('corporation_locations');
    }
}
