<?php

/*
 * This file is part of SeAT
 *
 * Copyright (C) 2015 to present Leon Jacobs
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

namespace Seat\Eveapi\Jobs\Contracts\Character;

use Seat\Eveapi\Jobs\AbstractAuthCharacterJob;
use Seat\Eveapi\Jobs\Universe\Structures\StructureBatch;
use Seat\Eveapi\Models\Contracts\CharacterContract;
use Seat\Eveapi\Models\Contracts\ContractDetail;

/**
 * Class Contracts.
 *
 * @package Seat\Eveapi\Jobs\Contracts\Character
 */
class Contracts extends AbstractAuthCharacterJob
{
    /**
     * @var string
     */
    protected $method = 'get';

    /**
     * @var string
     */
    protected $endpoint = '/characters/{character_id}/contracts/';

    /**
     * @var string
     */
    protected string $compatibility_date = "2025-07-20";

    /**
     * @var string
     */
    protected $scope = 'esi-contracts.read_character_contracts.v1';

    /**
     * @var array
     */
    protected $tags = ['character', 'contract'];

    /**
     * @var int
     */
    protected $page = 1;

    /**
     * Execute the job.
     *
     * @return void
     *
     * @throws \Exception
     * @throws \Throwable
     */
    public function handle()
    {

        $structure_batch = new StructureBatch();

        while (true) {

            $response = $this->retrieve([
                'character_id' => $this->getCharacterId(),
            ]);

            $contracts = $response->getBody();

            collect($contracts)->each(function ($contract) use ($structure_batch) {
                if ($contract->start_location_id) {
                    $structure_batch->addStructure($contract->start_location_id);
                }
                if ($contract->end_location_id) {
                    $structure_batch->addStructure($contract->end_location_id);
                }

                // Update or create the contract details.
                $model = ContractDetail::firstOrNew([
                    'contract_id' => $contract->contract_id,
                ]);

                $model->fromEsi($contract);
                $model->save();

                // Ensure the character is associated to this contract
                CharacterContract::firstOrCreate([
                    'character_id' => $this->getCharacterId(),
                    'contract_id' => $contract->contract_id,
                ]);

                // dispatch a job which will collect bids related to this contract
                if ($contract->type == 'auction' && $contract->status != 'deleted')
                    dispatch(new Bids($this->token, $contract->contract_id));

                // dispatch a job which will collect items related to this contract
                if ($contract->type != 'courier' && $contract->status != 'deleted' &&
                    $contract->volume > 0 && $model->lines->isEmpty())
                    dispatch(new Items($this->token, $contract->contract_id));
            });

            if (! $this->nextPage($response->getPagesCount())) {
                $structure_batch->submitJobs($this->getToken());
                break;
            }
        }
    }
}
