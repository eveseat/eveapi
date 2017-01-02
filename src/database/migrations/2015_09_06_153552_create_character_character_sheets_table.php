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

class CreateCharacterCharacterSheetsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {

        Schema::create('character_character_sheets', function (Blueprint $table) {

            $table->integer('characterID')->unique();
            $table->string('name');
            $table->integer('homeStationID');
            $table->dateTime('DoB');
            $table->string('race');
            $table->integer('bloodLineID');
            $table->string('bloodLine');
            $table->integer('ancestryID');
            $table->string('ancestry');
            $table->string('gender');
            $table->string('corporationName');
            $table->integer('corporationID');
            $table->string('allianceName')->nullable();
            $table->integer('allianceID')->nullable();
            $table->string('factionName')->nullable();
            $table->integer('factionID');
            $table->integer('cloneTypeID');
            $table->string('cloneName');
            $table->integer('cloneSkillPoints');
            $table->integer('freeSkillPoints');
            $table->integer('freeRespecs');
            $table->dateTime('cloneJumpDate');
            $table->dateTime('lastRespecDate');
            $table->dateTime('lastTimedRespec');
            $table->dateTime('remoteStationDate');
            $table->dateTime('jumpActivation');
            $table->dateTime('jumpFatigue');
            $table->dateTime('jumpLastUpdate');
            $table->decimal('balance', 30, 2)->nullable();    // Some rich bastards out there
            $table->integer('intelligence');
            $table->integer('memory');
            $table->integer('charisma');
            $table->integer('perception');
            $table->integer('willpower');

            // Indexes
            $table->primary('characterID');
            $table->index('corporationID');
            $table->index('allianceID');
            $table->index('name');

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

        Schema::drop('character_character_sheets');
    }
}
