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

class CreateCharacterContractsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {

        Schema::create('character_contracts', function (Blueprint $table) {

            $table->integer('characterID');
            $table->integer('contractID');
            $table->integer('issuerID');
            $table->integer('issuerCorpID');
            $table->integer('assigneeID');
            $table->integer('acceptorID');
            $table->integer('startStationID');
            $table->integer('endStationID');
            $table->string('type');
            $table->string('status');
            $table->string('title')->nullable();
            $table->integer('forCorp');
            $table->string('availability');
            $table->dateTime('dateIssued');
            $table->dateTime('dateExpired')->nullable();
            $table->dateTime('dateAccepted')->nullable();
            $table->integer('numDays');
            $table->dateTime('dateCompleted')->nullable();
            $table->decimal('price', 30, 2);
            $table->decimal('reward', 30, 2);
            $table->decimal('collateral', 30, 2);
            $table->decimal('buyout', 30, 2);
            $table->integer('volume');

            // Indexes
            $table->index('characterID');
            $table->index('contractID');
            $table->index('issuerID');

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

        Schema::drop('character_contracts');
    }
}
