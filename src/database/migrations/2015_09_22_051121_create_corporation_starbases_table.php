<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class CreateCorporationStarbasesTable extends Migration
{

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {

        Schema::create('corporation_starbases', function (Blueprint $table) {

            $table->integer('corporationID');
            $table->bigInteger('itemID')->unique();
            $table->integer('typeID');
            $table->bigInteger('locationID');
            $table->bigInteger('moonID');
            $table->integer('state');
            $table->dateTime('stateTimestamp');
            $table->dateTime('onlineTimestamp');
            $table->bigInteger('standingOwnerID');

            // Indexes
            $table->primary('itemID');
            $table->index('corporationID');

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

        Schema::drop('corporation_starbases');
    }
}
