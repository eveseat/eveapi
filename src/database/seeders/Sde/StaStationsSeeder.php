<?php

/*
 * This file is part of SeAT
 *
 * Copyright (C) 2015 to 2022 Leon Jacobs
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

use Illuminate\Contracts\Filesystem\FileNotFoundException;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Schema;
use Maatwebsite\Excel\Facades\Excel;
use Seat\Eveapi\Mapping\Sde\StaStationMapping;

class StaStationsSeeder extends Seeder
{
    public function run()
    {
        $this->createTable();

        $this->seedTable();
    }

    /**
     * Create mapDenormalize table structure.
     *
     * @return void
     */
    private function createTable()
    {
        Schema::dropIfExists('staStations');

        Schema::create('staStations', function (Blueprint $table) {
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
        });
    }

    /**
     * Seed table with csv content.
     *
     * @return void
     *
     * @throws \Illuminate\Contracts\Filesystem\FileNotFoundException
     */
    private function seedTable()
    {
        $file = storage_path('sde/staStations.csv');

        if (! file_exists($file))
            throw new FileNotFoundException("Unable to retrieve $file.");

        Excel::import(new StaStationMapping(), $file);
    }
}
