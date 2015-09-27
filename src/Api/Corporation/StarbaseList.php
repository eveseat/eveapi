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
use Seat\Eveapi\Models\Corporation\Starbase;
use Seat\Eveapi\Models\Corporation\StarbaseDetail as StarbaseDetailModel;

/**
 * Class StarbaseList
 * @package Seat\Eveapi\Api\Corporation
 */
class StarbaseList extends Base
{

    /**
     * Run the Update
     *
     * @return mixed|void
     */
    public function call()
    {

        $pheal = $this->setScope('corp')
            ->setCorporationID()->getPheal();

        $result = $pheal->StarbaseList();

        foreach ($result->starbases as $starbase) {

            $starbase_info = Starbase::firstOrNew([
                'corporationID' => $this->corporationID,
                'itemID'        => $starbase->itemID]);

            $starbase_info->fill([
                'typeID'          => $starbase->typeID,
                'locationID'      => $starbase->locationID,
                'moonID'          => $starbase->moonID,
                'state'           => $starbase->state,
                'stateTimestamp'  => $starbase->stateTimestamp,
                'onlineTimestamp' => $starbase->onlineTimestamp,
                'standingOwnerID' => $starbase->standingOwnerID
            ]);

            $starbase_info->save();
        }

        // Cleanup old Starbases
        Starbase::where('corporationID', $this->corporationID)
            ->whereNotIn('itemID', array_map(function ($starbase) {

                return $starbase->itemID;

            }, (array)$result->starbases))
            ->delete();

        // Cleanup old Starbase details.
        StarbaseDetailModel::where('corporationID', $this->corporationID)
            ->whereNotIn('itemID', array_map(function ($starbase) {

                return $starbase->itemID;

            }, (array)$result->starbases))
            ->delete();

        return;
    }
}
