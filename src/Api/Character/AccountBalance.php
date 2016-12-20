<?php
/*
This file is part of SeAT

Copyright (C) 2015, 2016  Leon Jacobs

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
use Seat\Eveapi\Models\Character\AccountBalance as AccountBalanceModel;

/**
 * Class AccountBalance
 * @package Seat\Eveapi\Api\Character
 */
class AccountBalance extends Base
{

    /**
     * Run the Update
     *
     * @return mixed|void
     */
    public function call()
    {

        $pheal = $this->setScope('char')->getPheal();

        foreach ($this->api_info->characters as $character) {

            $this->writeJobLog('accountbalance',
                'Processing characterID: ' . $character->characterID);

            $result = $pheal->AccountBalance([
                'characterID' => $character->characterID]);

            // I know, characters only have one account, but considering
            // how CCP operates (cough, dust, cough), lets just be sure
            // and loop over them in case...
            foreach ($result->accounts as $account) {

                $account_balance = AccountBalanceModel::firstOrNew([
                    'characterID' => $character->characterID,
                    'accountID'   => $account->accountID]);

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
