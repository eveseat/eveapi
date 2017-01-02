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

class CreateEveCharacterInfosTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {

        Schema::create('eve_character_infos', function (Blueprint $table) {

            $table->integer('characterID')->unique();
            $table->string('characterName');
            $table->string('race');
            $table->string('bloodline');
            $table->integer('bloodlineID');
            $table->string('ancestry');
            $table->integer('ancestryID');
            $table->integer('corporationID');
            $table->string('corporation');
            $table->dateTime('corporationDate');
            $table->decimal('securityStatus', 20, 13);

            // Some columns will only be filled if a
            // key/vcode pair is provided and valid,
            // so make them nullable in case its not
            $table->decimal('accountBalance', 30, 2)->nullable();    // Some rich bastards out there
            $table->integer('skillPoints')->nullable();
            $table->dateTime('nextTrainingEnds')->nullable();
            $table->string('shipName')->nullable();
            $table->integer('shipTypeID')->nullable();
            $table->string('shipTypeName')->nullable();
            $table->integer('allianceID')->nullable();
            $table->string('alliance')->nullable();
            $table->dateTime('allianceDate')->nullable();
            $table->string('lastKnownLocation')->nullable();

            // Index
            $table->primary('characterID');
            $table->index('characterName');

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

        Schema::drop('eve_character_infos');
    }
}
