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

namespace Seat\Eveapi\Api\Character;

use Seat\Eveapi\Api\Base;
use Seat\Eveapi\Models\CharacterMarketOrder;
use Seat\Eveapi\Models\EveApiKey;

/**
 * Class MarketOrders
 * @package Seat\Eveapi\Api\Character
 */
class MarketOrders extends Base
{

    /**
     * Run the Update
     *
     * @param \Seat\Eveapi\Models\EveApiKey $api_info
     */
    public function call(EveApiKey $api_info)
    {

        // Ofc, we need to process the update of all
        // of the characters on this key.
        foreach ($api_info->characters as $character) {

            $result = $this->setKey(
                $api_info->key_id, $api_info->v_code)
                ->getPheal()
                ->charScope
                ->MarketOrders([
                    'characterID' => $character->characterID]);

            // Update the Market Orders
            foreach ($result->orders as $order) {

                // Get or create the record...
                $order_info = CharacterMarketOrder::firstOrNew([
                    'charID'  => $character->characterID,
                    'orderID' => $order->orderID]);

                // ... and set its fields
                $order_info->fill([
                    'stationID'    => $order->stationID,
                    'volEntered'   => $order->volEntered,
                    'volRemaining' => $order->volRemaining,
                    'minVolume'    => $order->minVolume,
                    'orderState'   => $order->orderState,
                    'typeID'       => $order->typeID,
                    'range'        => $order->range,
                    'accountKey'   => $order->accountKey,
                    'duration'     => $order->duration,
                    'escrow'       => $order->escrow,
                    'price'        => $order->price,
                    'bid'          => $order->bid,
                    'issued'       => $order->issued
                ]);

                $order_info->save();
            }
        }

        return;
    }
}
