<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class CreateCharacterIndustryJobsTable extends Migration
{

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {

        Schema::create('character_industry_jobs', function (Blueprint $table) {

            $table->increments('id');

            $table->integer('characterID');
            $table->integer('jobID');
            $table->integer('installerID');
            $table->string('installerName');
            $table->integer('facilityID');
            $table->integer('solarSystemID');
            $table->string('solarSystemName');
            $table->integer('stationID');
            $table->integer('activityID');
            $table->bigInteger('blueprintID');
            $table->integer('blueprintTypeID');
            $table->string('blueprintTypeName');
            $table->integer('blueprintLocationID');
            $table->integer('outputLocationID');
            $table->integer('runs');
            $table->float('cost');
            $table->integer('teamID');
            $table->integer('licensedRuns');
            $table->integer('probability');
            $table->integer('productTypeID');
            $table->string('productTypeName');
            $table->integer('status');
            $table->integer('timeInSeconds');
            $table->dateTime('startDate');
            $table->dateTime('endDate');
            $table->dateTime('pauseDate');
            $table->dateTime('completedDate');
            $table->integer('completedCharacterID');
            $table->integer('successfulRuns');

            // Indexes
            $table->index('characterID');
            $table->index('jobID');

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

        Schema::drop('character_industry_jobs');
    }
}
