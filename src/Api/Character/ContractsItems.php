<?php
/*
The MIT License (MIT)

Copyright (c) 2015 Leon Jacobs
Copyright (c) 2015 eveseat

Permission is hereby granted, free of charge, to any person obtaining a copy
of this software and associated documentation files (the "Software"), to deal
in the Software without restriction, including without limitation the rights
to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
copies of the Software, and to permit persons to whom the Software is
furnished to do so, subject to the following conditions:

The above copyright notice and this permission notice shall be included in all
copies or substantial portions of the Software.

THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN THE
SOFTWARE.
*/

namespace Seat\Eveapi\Api\Character;

use Illuminate\Support\Facades\DB;
use Seat\Eveapi\Api\Base;
use Seat\Eveapi\Models\CharacterContractItems;
use Seat\Eveapi\Models\EveApiKey;

/**
 * Class ContractsItems
 * @package Seat\Eveapi\Api\Character
 */
class ContractsItems extends Base
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

            // Get a list of all of the contracts that do
            // not have their items updated yet. Since this
            // is hopefully only a one time thing, if the
            // id exists, we can assume the contract is up
            // to date.
            $contract_ids = DB::table('character_contracts')
                ->where('characterID', $character->characterID)
                ->whereNotIn('contractID', function ($query) use ($character) {

                    $query->select('contractID')
                        ->from('character_contract_items')
                        ->where('characterID', $character->characterID);

                })
                ->lists('contractID');

            // Process the contractID's as we have received them
            foreach ($contract_ids as $contract_id) {

                $result = $this->setKey(
                    $api_info->key_id, $api_info->v_code)
                    ->getPheal()
                    ->charScope
                    ->ContractItems([
                        'characterID' => $character->characterID,
                        'contractID'  => $contract_id]);

                // Finally, loop the results and populate the db
                foreach ($result->itemList as $item) {

                    CharacterContractItems::create([
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
