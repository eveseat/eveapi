<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class CreateCorporationCustomsOfficesTable extends Migration
{

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {

        Schema::create('corporation_customs_offices', function (Blueprint $table) {

            $table->increments('id');
            $table->integer('corporationID');
            $table->bigInteger('itemID');
            $table->bigInteger('solarSystemID');
            $table->string('solarSystemName');
            $table->integer('reinforceHour');
            $table->boolean('allowAlliance');
            $table->boolean('allowStandings');
            $table->double('standingLevel');
            $table->double('taxRateAlliance');
            $table->double('taxRateCorp');
            $table->double('taxRateStandingHigh');
            $table->double('taxRateStandingGood');
            $table->double('taxRateStandingNeutral');
            $table->double('taxRateStandingBad');
            $table->double('taxRateStandingHorrible');

            // Indexes
            $table->index('corporationID');
            $table->index('solarSystemID');
            $table->index('itemID');

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

        Schema::drop('corporation_customs_offices');
    }
}
