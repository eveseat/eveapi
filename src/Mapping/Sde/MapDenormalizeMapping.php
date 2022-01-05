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
use Seat\Eveapi\Models\Sde\MapDenormalize;

/**
 * MapDenormalizeMapping.
 *
 * Used to import csv data into mapDenormalize table.
 * CSV file must be formatted using Fuzzwork format.
 *
 * @url https://www.fuzzwork.co.uk
 */
class MapDenormalizeMapping extends AbstractFuzzworkMapping
{
    /**
     * @param  array  $row
     * @return Model|Model[]|null
     */
    public function model(array $row)
    {
        return (new MapDenormalize([
            'itemID'          => $row[0],
            'typeID'          => $row[1],
            'groupID'         => $row[2],
            'solarSystemID'   => $row[3],
            'constellationID' => $row[4],
            'regionID'        => $row[5],
            'orbitID'         => $row[6],
            'x'               => $row[7],
            'y'               => $row[8],
            'z'               => $row[9],
            'radius'          => $row[10],
            'itemName'        => $row[11],
            'security'        => $row[12],
            'celestialIndex'  => $row[13],
            'orbitIndex'      => $row[14],
        ]))->bypassReadOnly();
    }
}
