<?php

/*
 * This file is part of SeAT
 *
 * Copyright (C) 2015, 2016, 2017  Leon Jacobs
 *
 * This program is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 2 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License along
 * with this program; if not, write to the Free Software Foundation, Inc.,
 * 51 Franklin Street, Fifth Floor, Boston, MA 02110-1301 USA.
 */

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class CreateCorporationIndustryJobsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {

        Schema::create('corporation_industry_jobs', function (Blueprint $table) {

            $table->integer('corporationID');
            $table->integer('jobID')->unique();
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
            $table->primary('jobID');
            $table->index('corporationID');
            $table->index('installerID');

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

        Schema::drop('corporation_industry_jobs');
    }
}
