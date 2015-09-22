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

use Illuminate\Support\Facades\DB;
use Seat\Eveapi\Api\Base;
use Seat\Eveapi\Models\CorporationStarbaseDetail;

/**
 * Class StarbaseDetail
 * @package Seat\Eveapi\Api\Corporation
 */
class StarbaseDetail extends Base
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

        $starbase_ids = DB::table('corporation_starbases')
            ->where('corporationID', $this->corporationID)
            ->lists('itemID');

        foreach ($starbase_ids as $starbase_id) {

            $result = $pheal->StarbaseDetail(['itemID' => $starbase_id]);

            $detail_info = CorporationStarbaseDetail::firstOrNew([
                'corporationID' => $this->corporationID,
                'itemID'        => $starbase_id
            ]);

            $detail_info->fill([
                'state'                   => $result->state,
                'stateTimestamp'          => $result->stateTimestamp,
                'onlineTimestamp'         => $result->onlineTimestamp,
                'usageFlags'              => $result->generalSettings->usageFlags,
                'deployFlags'             => $result->generalSettings->deployFlags,
                'allowCorporationMembers' => $result->generalSettings->allowCorporationMembers,
                'allowAllianceMembers'    => $result->generalSettings->allowAllianceMembers,
                'useStandingsFrom'        => $result->combatSettings->useStandingsFrom->ownerID,
                'onStandingDrop'          => $result->combatSettings->onStandingDrop->standing,
                'onStatusDropEnabled'     => $result->combatSettings->onStatusDrop->enabled,
                'onStatusDropStanding'    => $result->combatSettings->onStatusDrop->standing,
                'onAggression'            => $result->combatSettings->onAggression->enabled,
                'onCorporationWar'        => $result->combatSettings->onCorporationWar->enabled,
                'fuelBlocks'              => $this->get_fuel('fuelBlocks', $result->fuel),
                'strontium'               => $this->get_fuel('strontium', $result->fuel),
                'starbaseCharter'         => $this->get_fuel('starbaseCharter', $result->fuel)
            ]);

            $detail_info->save();

        }

        return;
    }

    /**
     * Determines the amount of a specific fuel type that is
     * in the API response.
     *
     * @param $type
     * @param $fuel_info
     *
     * @return int|void
     */
    public function get_fuel($type, $fuel_info)
    {

        $strontium = 0;
        $fuelBlocks = 0;
        $starbaseCharter = 0;

        foreach ($fuel_info as $fuel) {

            if ($fuel->typeID == 16275)
                $strontium = $fuel->quantity;

            // fuelBlock typeIDs
            // 4051     Caldari Fuel Block
            // 4246     Minmatar Fuel Block
            // 4247     Amarr Fuel Block
            // 4312     Gallente Fuel Block
            if (in_array($fuel->typeID, ['4051', '4246', '4247', '4312']))
                $fuelBlocks = $fuel->quantity;

            // starbaseCharter typeIDs
            // 24592    Amarr Empire Starbase Charter
            // 24593    Caldari State Starbase Charter
            // 24594    Gallente Federation Starbase Charter
            // 24595    Minmatar Republic Starbase Charter
            // 24596    Khanid Kingdom Starbase Charter
            // 24597    Ammatar Mandate Starbase Charter
            if (in_array($fuel->typeID, ['24592', '24593', '24594', '24595', '24596', '24597']))
                $starbaseCharter = $fuel->quantity;
        }

        // Return the fuel type requested
        switch ($type) {

            case 'fuelBlocks':
                return $fuelBlocks;

            case 'strontium':
                return $strontium;

            case 'starbaseCharter':
                return $starbaseCharter;
        }

        return;

    }
}
