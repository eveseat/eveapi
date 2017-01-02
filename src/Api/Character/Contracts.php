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

namespace Seat\Eveapi\Api\Character;

use Seat\Eveapi\Api\Base;
use Seat\Eveapi\Models\Character\Contract;

/**
 * Class Contracts.
 * @package Seat\Eveapi\Api\Character
 */
class Contracts extends Base
{
    /**
     * Run the Update.
     *
     * @return mixed|void
     */
    public function call()
    {

        $pheal = $this->setScope('char')->getPheal();

        foreach ($this->api_info->characters as $character) {

            $this->writeJobLog('contracts',
                'Processing characterID: ' . $character->characterID);

            $result = $pheal->Contracts([
                'characterID' => $character->characterID, ]);

            $this->writeJobLog('contracts',
                'API responded with ' . count($result->contractList) . ' contracts');

            foreach ($result->contractList as $contract) {

                $contract_data = Contract::firstOrNew([
                    'characterID' => $character->characterID,
                    'contractID'  => $contract->contractID,
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
                    'dateAccepted'   => $contract->dateAccepted === '' ?
                        null : $contract->dateAccepted,
                    'numDays'        => $contract->numDays,
                    'dateCompleted'  => $contract->dateCompleted === '' ?
                        null : $contract->dateCompleted,
                    'price'          => $contract->price,
                    'reward'         => $contract->reward,
                    'collateral'     => $contract->collateral,
                    'buyout'         => $contract->buyout,
                    'volume'         => $contract->volume,
                ]);

                $contract_data->save();
            }

        } // Foreach Character

    }
}
