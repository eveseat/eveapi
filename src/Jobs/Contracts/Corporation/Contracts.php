<?php

/*
 * This file is part of SeAT
 *
 * Copyright (C) 2015 to 2022 Leon Jacobs
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

namespace Seat\Eveapi\Jobs\Contracts\Corporation;

use Seat\Eveapi\Jobs\AbstractAuthCorporationJob;
use Seat\Eveapi\Models\Contracts\ContractDetail;
use Seat\Eveapi\Models\Contracts\CorporationContract;

/**
 * Class Contracts.
 *
 * @package Seat\Eveapi\Jobs\Contracts\Corporation
 */
class Contracts extends AbstractAuthCorporationJob
{
    /**
     * @var string
     */
    protected $method = 'get';

    /**
     * @var string
     */
    protected $endpoint = '/corporations/{corporation_id}/contracts/';

    /**
     * @var string
     */
    protected $version = 'v1';

    /**
     * @var string
     */
    protected $scope = 'esi-contracts.read_corporation_contracts.v1';

    /**
     * @var array
     */
    protected $tags = ['corporation', 'contract'];

    /**
     * @var int
     */
    protected $page = 1;

    /**
     * Execute the job.
     *
     * @return void
     *
     * @throws \Throwable
     */
    public function handle()
    {
        while (true) {

            $response = $this->retrieve([
                'corporation_id' => $this->getCorporationId(),
            ]);

            if ($response->isFromCache() &&
                CorporationContract::where('corporation_id', $this->getCorporationId())->count() > 0)
                return;

            $contracts = $response->getBody();

            collect($contracts)->each(function ($contract) {

                // Update or create the contract details.
                $model = ContractDetail::firstOrNew([
                    'contract_id' => $contract->contract_id,
                ]);

                $model->fromEsi($contract);
                $model->save();

                // Ensure the character is associated to this contract
                CorporationContract::firstOrCreate([
                    'corporation_id' => $this->getCorporationId(),
                    'contract_id'    => $contract->contract_id,
                ]);

                // dispatch a job which will collect bids related to this contract
                if ($contract->type == 'auction' && $contract->status != 'deleted')
                    dispatch(new Bids($this->getCorporationId(), $this->token, $contract->contract_id));

                // dispatch a job which will collect items related to this contract
                if ($contract->type != 'courier' && $contract->status != 'deleted' &&
                    $contract->volume > 0 && $model->lines->isEmpty())
                    dispatch(new Items($this->getCorporationId(), $this->token, $contract->contract_id));
            });

            if (! $this->nextPage($response->getPagesCount()))
                break;
        }
    }
}
