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
use Seat\Eveapi\Models\CharacterContract;
use Seat\Eveapi\Models\EveApiKey;

/**
 * Class Contracts
 * @package Seat\Eveapi\Api\Character
 */
class Contracts extends Base
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
                ->Contracts([
                    'characterID' => $character->characterID]);

            // Add new contracts
            foreach ($result->contractList as $contract) {

                $contract_data = CharacterContract::firstOrNew([
                    'characterID' => $character->characterID,
                    'contractID'  => $contract->contractID
                ]);

                $contract_data->fill([
                    'issuerID'       => $contract->issuerID,
                    'issuerCorpID'   => $contract->issuerCorpID,
                    'assigneeID'     => $contract->assigneeID,
                    'acceptorID'     => $contract->acceptorID,
                    'startStationID' => $contract->startStationID,
                    'endStationID'   => $contract->endStationID,
                    'type'           => $contract->type,
                    'status'         => $contract->status,
                    'title'          => $contract->title,
                    'forCorp'        => $contract->forCorp,
                    'availability'   => $contract->availability,
                    'dateIssued'     => $contract->dateIssued,
                    'dateExpired'    => $contract->dateExpired,
                    'dateAccepted'   => $contract->dateAccepted,
                    'numDays'        => $contract->numDays,
                    'dateCompleted'  => $contract->dateCompleted,
                    'price'          => $contract->price,
                    'reward'         => $contract->reward,
                    'collateral'     => $contract->collateral,
                    'buyout'         => $contract->buyout,
                    'volume'         => $contract->volume
                ]);

                $contract_data->save();
            }

        } // Foreach Character

        return;
    }
}
