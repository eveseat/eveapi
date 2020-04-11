<?php

/*
 * This file is part of SeAT
 *
 * Copyright (C) 2015 to 2020 Leon Jacobs
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

use Seat\Eseye\Exceptions\RequestFailedException;
use Seat\Eveapi\Jobs\AbstractCorporationJob;
use Seat\Eveapi\Models\Contracts\ContractBid;
use Seat\Eveapi\Models\Contracts\ContractDetail;
use Seat\Eveapi\Models\Contracts\CorporationContract;

/**
 * Class Bids.
 * @package Seat\Eveapi\Jobs\Contracts\Corporation
 */
class Bids extends AbstractCorporationJob
{
    /**
     * @var string
     */
    protected $method = 'get';

    /**
     * @var string
     */
    protected $endpoint = '/corporations/{corporation_id}/contracts/{contract_id}/bids/';

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
    protected $tags = ['corporation', 'contracts', 'bids'];

    /**
     * @var int
     */
    protected $page = 1;

    /**
     * Execute the job.
     *
     * @return void
     * @throws \Throwable
     */
    protected function job(): void
    {
        $unfinished_auctions = CorporationContract::join('contract_details',
            'corporation_contracts.contract_id', '=',
            'contract_details.contract_id')
            ->where('corporation_id', $this->getCorporationId())
            ->where('type', 'auction')
            ->whereNotIn('status', ['finished', 'deleted'])
            ->pluck('corporation_contracts.contract_id');

        $unfinished_auctions->each(function ($contract_id) {

            while (true) {

                try {
                    $bids = $this->retrieve([
                        'corporation_id' => $this->getCorporationId(),
                        'contract_id'    => $contract_id,
                    ]);

                    if ($bids->isCachedLoad()) return;

                    collect($bids)->each(function ($bid) use ($contract_id) {

                        ContractBid::firstOrCreate([
                            'bid_id' => $bid->bid_id,
                        ], [
                            'contract_id' => $contract_id,
                            'bidder_id' => $bid->bidder_id,
                            'date_bid' => carbon($bid->date_bid),
                            'amount' => $bid->amount,
                        ]);
                    });

                    if (! $this->nextPage($bids->pages))
                        break;

                } catch (RequestFailedException $e) {
                    if (strtolower($e->getError()) == 'contract not found') {
                        ContractDetail::where('contract_id', $contract_id)
                            ->update([
                                'status' => 'deleted',
                            ]);

                        break;
                    }

                    throw $e;
                }
            }

            // reset the page back to page one for the next contract
            $this->page = 1;
        });
    }
}
