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

class CreateCorporationStarbaseDetailsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {

        Schema::create('corporation_starbase_details', function (Blueprint $table) {

            $table->integer('corporationID');
            $table->bigInteger('itemID')->unique();
            $table->integer('state');
            $table->dateTime('stateTimestamp');
            $table->dateTime('onlineTimestamp');
            $table->integer('usageFlags');
            $table->integer('deployFlags');
            $table->integer('allowCorporationMembers');
            $table->integer('allowAllianceMembers');
            $table->integer('useStandingsFrom');
            $table->integer('onStandingDrop');
            $table->integer('onStatusDropEnabled');
            $table->integer('onStatusDropStanding');
            $table->integer('onAggression');
            $table->integer('onCorporationWar');
            $table->integer('fuelBlocks')->default(0);
            $table->integer('strontium')->default(0);
            $table->integer('starbaseCharter')->nullable();

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

        Schema::drop('corporation_starbase_details');
    }
}
