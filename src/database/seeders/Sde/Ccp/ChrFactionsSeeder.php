<?php

/*
 * This file is part of SeAT
 *
 * Copyright (C) 2015 to present Leon Jacobs
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

namespace Seat\Eveapi\Database\Seeders\Sde\Ccp;

use Illuminate\Database\Schema\Blueprint;
use Seat\Eveapi\Mapping\Sde\AbstractSdeMapping;
use Seat\Eveapi\Database\Seeders\Sde\AbstractSdeSeeder;
use Seat\Eveapi\Mapping\Sde\Ccp\ChrFactionMapping;
use Seat\Eveapi\Models\Sde\ChrFaction;

class ChrFactionsSeeder extends AbstractSdeSeeder
{

    protected const FILENAME = "factions.jsonl";



    /**
     * Define seeder related SDE table structure.
     *
     * @param  \Illuminate\Database\Schema\Blueprint  $table
     * @return void
     */
    protected function getSdeTableDefinition(Blueprint $table): void
    {
        $table->integer('factionID')->primary();
        $table->string('factionName', 100)->nullable();
        $table->string('description', 2000)->nullable();
        $table->integer('raceIDs')->nullable();
        $table->integer('solarSystemID')->nullable();
        $table->integer('corporationID')->nullable();
        $table->integer('sizeFactor')->nullable();
        $table->integer('stationCount')->nullable();
        $table->integer('stationSystemCount')->nullable();
        $table->integer('militiaCorporationID')->nullable();
        $table->integer('iconID')->nullable();
    }

    /**
     * The mapping instance which must be used to seed table with SDE dump.
     *
     * @return \Seat\Eveapi\Mapping\Sde\AbstractSdeMapping
     */
    protected function getMappingClass(): AbstractSdeMapping
    {
        return new ChrFactionMapping();
    }



    public function insert($arr)
    {
        return ChrFaction::insert($arr);
    }
}
