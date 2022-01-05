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

namespace Seat\Eveapi\Mapping\Sde;

use Illuminate\Database\Eloquent\Model;
use Seat\Eveapi\Models\Sde\StaStation;

/**
 * StaStationMapping.
 *
 * Used to import csv data into staStations table.
 * CSV file must be formatted using Fuzzwork format.
 *
 * @url https://www.fuzzwork.co.uk
 */
class StaStationMapping extends AbstractFuzzworkMapping
{
    /**
     * @param  array  $row
     * @return Model|Model[]|null
     */
    public function model(array $row)
    {
        return (new StaStation([
            'stationID'                => $row[0],
            'security'                 => $row[1],
            'dockingCostPerVolume'     => $row[2],
            'maxShipVolumeDockable'    => $row[3],
            'officeRentalCost'         => $row[4],
            'operationID'              => $row[5],
            'stationTypeID'            => $row[6],
            'corporationID'            => $row[7],
            'solarSystemID'            => $row[8],
            'constellationID'          => $row[9],
            'regionID'                 => $row[10],
            'stationName'              => $row[11],
            'x'                        => $row[12],
            'y'                        => $row[13],
            'z'                        => $row[14],
            'reprocessingEfficiency'   => $row[15],
            'reprocessingStationsTake' => $row[16],
            'reprocessingHangarFlag'   => $row[17],
        ]))->bypassReadOnly();
    }
}
