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

use Illuminate\Support\Facades\DB;
use Seat\Eveapi\Api\Base;
use Seat\Eveapi\Models\Character\ContractItems;

/**
 * Class ContractsItems
 * @package Seat\Eveapi\Api\Character
 */
class ContractsItems extends Base
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

            // Get a list of all of the contracts that do
            // not have their items updated yet. Since this
            // is hopefully only a one time thing, if the
            // id exists, we can assume the contract is up
            // to date.
            $contract_ids = DB::table('character_contracts')
                ->where('characterID', $character->characterID)
                ->where('type', '<>', 'Courier')
                ->whereNotIn('contractID', function ($query) use ($character) {

                    $query->select('contractID')
                        ->from('character_contract_items')
                        ->where('characterID', $character->characterID);

                })
                ->lists('contractID');

            // Process the contractID's as we have received them
            foreach ($contract_ids as $contract_id) {

                $result = $pheal->ContractItems([
                    'characterID' => $character->characterID,
                    'contractID'  => $contract_id]);

                foreach ($result->itemList as $item) {

                    ContractItems::create([
                        'characterID' => $character->characterID,
                        'contractID'  => $contract_id,
                        'recordID'    => $item->recordID,
                        'typeID'      => $item->typeID,
                        'quantity'    => $item->quantity,
                        'rawQuantity' => isset($item->rawQuantity) ?
                            $item->rawQuantity : 0,
                        'singleton'   => $item->singleton,
                        'included'    => $item->included
                    ]);

                } // Foreach ItemList

            } // Foreach ContractID

        } // Foreach Character

        return;
    }
}
