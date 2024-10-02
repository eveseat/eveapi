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

namespace Seat\Eveapi\Database\Seeders\Sde;

use Illuminate\Database\Schema\Blueprint;
use Seat\Eveapi\Mapping\Sde\AbstractFuzzworkMapping;
use Seat\Eveapi\Mapping\Sde\StaStationMapping;

class StaStationsSeeder extends AbstractSdeSeeder
{
    /**
     * Define seeder related SDE table structure.
     *
     * @param  \Illuminate\Database\Schema\Blueprint  $table
     * @return void
     */
    protected function getSdeTableDefinition(Blueprint $table): void
    {
        $table->integer('stationID')->primary();
        $table->double('security');
        $table->double('dockingCostPerVolume');
        $table->double('maxShipVolumeDockable');
        $table->double('officeRentalCost');
        $table->integer('operationID');
        $table->integer('stationTypeID');
        $table->bigInteger('corporationID');
        $table->integer('solarSystemID');
        $table->integer('constellationID');
        $table->integer('regionID');
        $table->string('stationName', 100);
        $table->double('x');
        $table->double('y');
        $table->double('z');
        $table->double('reprocessingEfficiency');
        $table->double('reprocessingStationsTake');
        $table->integer('reprocessingHangarFlag');
    }

    /**
     * The mapping instance which must be used to seed table with SDE dump.
     *
     * @return \Seat\Eveapi\Mapping\Sde\AbstractFuzzworkMapping
     */
    protected function getMappingClass(): AbstractFuzzworkMapping
    {
        return new StaStationMapping();
    }
}
