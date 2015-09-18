<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class CreateCharacterPlanetaryLinksTable extends Migration
{

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {

        Schema::create('character_planetary_links', function (Blueprint $table) {

            $table->integer('ownerID');
            $table->integer('planetID');
            $table->bigInteger('sourcePinID');
            $table->bigInteger('destinationPinID');
            $table->integer('linkLevel');

            // Indexes
            $table->index('sourcePinID');
            $table->index('destinationPinID');
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

        Schema::drop('character_planetary_links');
    }
}
