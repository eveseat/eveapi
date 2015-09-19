<?php
/*
This file is part of SeAT

Copyright (C) 2015  Leon Jacobs

This program is free software; you can redistribute it and/or modify
it under the terms of the GNU General Public License as published by
the Free Software Foundation; either version 2 of the License, or
(at your option) any later version.

This program is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
GNU General Public License for more details.

You should have received a copy of the GNU General Public License along
with this program; if not, write to the Free Software Foundation, Inc.,
51 Franklin Street, Fifth Floor, Boston, MA 02110-1301 USA.
*/

namespace Seat\Eveapi\Traits;

use Illuminate\Support\Facades\DB;

/**
 * Class Utils
 * @package Seat\Eveapi\Traits
 */
trait Utils
{

    /**
     * Return a MD5 hash to be used for transactionID's
     *
     * @param $owner_id
     * @param $date
     * @param $seed1
     * @param $seed2
     *
     * @return string
     */
    public function hash_transaction($owner_id, $date, $seed1, $seed2)
    {

        return md5(implode(',', [$owner_id, $date, $seed1, $seed2]));
    }

    /**
     * Finds the itemID (as mapID) and itemName (as mapName)
     * of the celestial closest to the x, y, z in a given
     * solar system.
     *
     * @param $solar_system_id
     * @param $x
     * @param $y
     * @param $z
     *
     * @return array
     */
    public function find_nearest_celestial($solar_system_id, $x, $y, $z)
    {

        // Querying mapDenormalized with [1] we can see
        // the available different group types in the
        // table is basically:
        //
        //        groupID	typeName
        //        ------------------
        //        3	        Region
        //        4	        Constellation
        //        5	        Solar System
        //        6	        Sun
        //        7	        Planet
        //        8	        Moon
        //        9	        Asteroid Belt
        //        10	    Stargate
        //        15	    Caldari Logistics Station
        //        995	    EVE Gate

        // For 'nearest to' resolution we will only be
        // matching coordinates in groups 6,7,8,9 and 10.

        // [1] select `invTypes`.`groupID`, `invTypes`.`typeName`,
        // `mapDenormalize`.`itemName` from `mapDenormalize`
        // join `invTypes` on `mapDenormalize`.`groupID` = `invTypes`.`groupID`
        // group by `invTypes`.`typeName` order by `mapDenormalize`.`groupID`;

        // The basic idea when determining the closest celestial
        // is to calculate the closest celestial to the x, y, z's
        // that we have. For that, we have to start with the max
        // possible distance, infinity.
        $closest_distance = INF;

        // As a response, we will return an array with
        // the closest ID and name from mapDenormallized
        $response = [
            'mapID'   => null,
            'mapName' => null
        ];

        $possible_celestials = DB::table('mapDenormalize')
            ->where('solarSystemID', $solar_system_id)
            ->whereIn('groupID', [6, 7, 8, 9, 10])
            ->get();

        foreach ($possible_celestials as $celestial) {

            // See: http://math.stackexchange.com/a/42642
            $distance = sqrt(
                pow(($x - $celestial->x), 2) + pow(($y - $celestial->y), 2) + pow(($z - $celestial->z), 2));

            // Are we there yet?
            if ($distance < $closest_distance) {

                $response = [
                    'mapID'   => $celestial->itemID,
                    'mapName' => $celestial->itemName
                ];
            }
        }

        return $response;
    }
}
