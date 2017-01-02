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

namespace Seat\Eveapi\Api\Eve;

use Seat\Eveapi\Api\Base;
use Seat\Eveapi\Models\Eve\ConquerableStationList as ConquerableStationListModel;

/**
 * Class ConquerableStationList.
 * @package Seat\Eveapi\Server
 */
class ConquerableStationList extends Base
{
    /**
     * Run the Update.
     */
    public function call()
    {

        $result = $this->setScope('eve')
            ->getPheal()
            ->ConquerableStationList();

        foreach ($result->outposts as $outpost) {

            $station = ConquerableStationListModel::firstOrNew([
                'stationID' => $outpost->stationID, ]);

            $station->fill([
                'stationID'       => $outpost->stationID,
                'stationName'     => $outpost->stationName,
                'stationTypeID'   => $outpost->stationTypeID,
                'solarSystemID'   => $outpost->solarSystemID,
                'corporationID'   => $outpost->corporationID,
                'corporationName' => $outpost->corporationName,
            ]);

            $station->save();
        }

    }
}
