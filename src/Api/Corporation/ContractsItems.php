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

namespace Seat\Eveapi\Api\Corporation;

use Illuminate\Support\Facades\DB;
use Seat\Eveapi\Api\Base;
use Seat\Eveapi\Models\Corporation\ContractItem;

/**
 * Class ContractsItems.
 * @package Seat\Eveapi\Api\Corporation
 */
class ContractsItems extends Base
{
    /**
     * Run the Update.
     *
     * @return mixed|void
     */
    public function call()
    {

        $pheal = $this->setScope('corp')
            ->setCorporationID()->getPheal();

        // Get a list of all of the contracts that do
        // not have their items updated yet. Since this
        // is hopefully only a one time thing, if the
        // id exists, we can assume the contract is up
        // to date.

        // This endpoint is a little strange. The ContractList
        // response contains contracts issued by the alliance
        // a corporation is part of. When you try and query
        // the ContractItems endpoint for one of those
        // contracts, the API will respond with a error:
        // <error code="134">Invalid or missing contractID.</error>
        // So, for now, limit this call to contracts where
        // the issuerCorpID or assigneeID is the same as
        // the current corporationID.
        //
        // See: https://forums.eveonline.com/default.aspx?g=posts&m=5791284#post5791284

        $contract_ids = DB::table('corporation_contracts')
            ->where('type', '<>', 'Courier')
            ->where('corporationID', $this->corporationID)
            ->whereNotIn('contractID', function ($query) {

                $query->select('contractID')
                    ->from('corporation_contract_items')
                    ->where('corporationID', $this->corporationID);

            })
            ->pluck('contractID');

        $this->writeJobLog('contractsitems', 'Updating ' .
            count($contract_ids) . ' contracts');

        // Process the contractID's as we have received them
        foreach ($contract_ids as $contract_id) {

            $result = $pheal->ContractItems([
                'contractID' => $contract_id, ]);

            foreach ($result->itemList as $item) {

                ContractItem::create([
                    'corporationID' => $this->corporationID,
                    'contractID'    => $contract_id,
                    'recordID'      => $item->recordID,
                    'typeID'        => $item->typeID,
                    'quantity'      => $item->quantity,
                    'rawQuantity'   => isset($item->rawQuantity) ?
                        $item->rawQuantity : 0,
                    'singleton'     => $item->singleton,
                    'included'      => $item->included,
                ]);

            } // Foreach ItemList

        } // Foreach ContractID

    }
}
