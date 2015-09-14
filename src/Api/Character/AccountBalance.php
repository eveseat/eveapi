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
use Seat\Eveapi\Models\CharacterAccountBalance;
use Seat\Eveapi\Models\EveApiKey;

/**
 * Class AccountBalance
 * @package Seat\Eveapi\Api\Character
 */
class AccountBalance extends Base
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
                ->AccountBalance([
                    'characterID' => $character->characterID]);

            // I know, characters only have one account, but considering
            // how CCP operates (cough, dust, cough), lets just be sure
            // and loop over them in case...
            foreach ($result->accounts as $account) {

                // Get or create the record...
                $account_balance = CharacterAccountBalance::firstOrNew([
                    'characterID' => $character->characterID,
                    'accountID'   => $account->accountID]);

                // ... and set its fields
                $account_balance->fill([
                    'accountKey' => $account->accountKey,
                    'balance'    => $account->balance
                ]);

                $account_balance->save();
            }
        }

        return;
    }
}
