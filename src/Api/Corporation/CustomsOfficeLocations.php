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

namespace Seat\Eveapi\Api\Corporation;

use Seat\Eveapi\Api\Base;
use Seat\Eveapi\Models\CorporationCustomsOffice;
use Seat\Eveapi\Models\CorporationCustomsOfficeLocation;
use Seat\Eveapi\Traits\Utils;

/**
 * Class CustomsOfficeLocations
 * @package Seat\Eveapi\Api\Corporation
 */
class CustomsOfficeLocations extends Base
{

    use Utils;

    /**
     * Run the Update
     *
     * @return mixed|void
     */
    public function call()
    {

        $pheal = $this->setScope('corp')
            ->setCorporationID()->getPheal();

        // Looking at the CustomsOffice update, we see that
        // the table is cleaned up beforehand as the
        // itemID's may change for assets. For this call
        // to the Locations endpoint, we will also have
        // to cleanup as we will be referencing the same
        // itemID's
        CorporationCustomsOfficeLocation::where(
            'corporationID', $this->corporationID)
            ->delete();

        // We get an array of items ID's that is keyed
        // by the itemID's so that we can later use this
        // same array to lookup the locationID for the
        // nearest_celestial lookup.
        $item_ids = CorporationCustomsOffice::where(
            'corporationID', $this->corporationID)
            ->get()
            ->keyBy('itemID')
            ->toArray();

        // Chunk the requests to the API as the ids field
        // could get too long with all of the bigInts inside
        // of the query string.
        foreach (array_chunk($item_ids, 100) as $items) {

            // Apply array_column so that we only pass the
            // itemID's in the query string to the API
            $result = $pheal->Locations([
                'ids' => implode(',', array_column($items, 'itemID'))]);

            foreach ($result->locations as $location) {

                $nearest_celestial = $this->find_nearest_celestial(
                    $item_ids[$location->itemID]['locationID'],
                    $location->x,
                    $location->y,
                    $location->z
                );

                CorporationCustomsOfficeLocation::create([
                    'corporationID' => $this->corporationID,
                    'itemID'        => $location->itemID,
                    'itemName'      => $location->itemName,
                    'x'             => $location->x,
                    'y'             => $location->y,
                    'z'             => $location->z,
                    'mapID'         => $nearest_celestial['mapID'],
                    'mapName'       => $nearest_celestial['mapName']
                ]);

            } // Foreach Location
        }

        return;
    }
}
