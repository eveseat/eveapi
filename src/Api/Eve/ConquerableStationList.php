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

namespace Seat\Eveapi\Api\Eve;

use Seat\Eveapi\Api\Base;
use Seat\Eveapi\Models\EveConquerableStationList;

/**
 * Class ConquerableStationList
 * @package Seat\Eveapi\Server
 */
class ConquerableStationList extends Base
{

    /**
     * Run the Update
     */
    public function call()
    {

        $result = $this->getPheal()
            ->eveScope
            ->ConquerableStationList();

        foreach ($result->outposts as $outpost) {

            // Get or create the Outpost...
            $station = EveConquerableStationList::firstOrNew([
                'stationID' => $outpost->stationID]);

            // ... and set its fields
            $station->fill([
                'stationID'       => $outpost->stationID,
                'stationName'     => $outpost->stationName,
                'stationTypeID'   => $outpost->stationTypeID,
                'solarSystemID'   => $outpost->solarSystemID,
                'corporationID'   => $outpost->corporationID,
                'corporationName' => $outpost->corporationName
            ]);

            $station->save();
        }

        return;
    }

}