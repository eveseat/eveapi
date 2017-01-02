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
