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

use Illuminate\Support\Facades\Redis;
use Seat\Eseye\Exceptions\RequestFailedException;
use Seat\Eveapi\Exception\DeletedContractException;
use Seat\Eveapi\Exception\InvalidContractTypeException;
use Seat\Eveapi\Jobs\AbstractAuthCorporationJob;
use Seat\Eveapi\Models\Contracts\ContractBid;
use Seat\Eveapi\Models\Contracts\ContractDetail;
use Seat\Eveapi\Models\RefreshToken;

/**
 * Class Bids.
 *
 * @package Seat\Eveapi\Jobs\Contracts\Corporation
 */
class Bids extends AbstractAuthCorporationJob
{
    /**
     * The number of seconds for a single throttle cycle.
     */
    const DELAY = 12;

    /**
     * The maximum number of requests that can be made per
     * throttling cycle.
     */
    const REQUESTS_LIMIT = 15;

    /**
     * @var int
     */
    protected $contract_id;

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
    protected $tags = ['corporation', 'contract'];

    /**
     * @var int
     */
    protected $page = 1;

    /**
     * Bids constructor.
     *
     * @param  int  $corporation_id
     * @param  \Seat\Eveapi\Models\RefreshToken  $token
     * @param  int  $contract_id
     */
    public function __construct(int $corporation_id, RefreshToken $token, int $contract_id)
    {
        $this->contract_id = $contract_id;

        array_push($this->tags, $contract_id);

        parent::__construct($corporation_id, $token);
    }

    /**
     * @return string
     */
    public function displayName(): string
    {
        return 'Retrieve corporation contract bids';
    }

    /**
     * Execute the job.
     *
     * @return void
     *
     * @throws \Throwable
     */
    public function handle()
    {
        $contract = ContractDetail::find($this->contract_id);

        // this job can only work with auction contracts
        if ($contract->type !== 'auction')
            throw new InvalidContractTypeException();

        // this job can only work with un-deleted contracts
        if ($contract->status == 'deleted')
            throw new DeletedContractException();

        Redis::throttle(implode(':', ['corporations', $this->getCorporationId(), 'contracts']))
            ->allow(self::REQUESTS_LIMIT)
            ->every(self::DELAY)
            ->then(function () {

                while (true) {
                    try {
                        $response = $this->retrieve([
                            'corporation_id' => $this->getCorporationId(),
                            'contract_id' => $this->contract_id,
                        ]);

                        if ($response->isFromCache() &&
                            ContractBid::where('contract_id', $this->contract_id)->count() > 0)
                            return;

                        $bids = $response->getBody();

                        collect($bids)->each(function ($bid) {

                            ContractBid::firstOrCreate([
                                'bid_id' => $bid->bid_id,
                            ], [
                                'contract_id' => $this->contract_id,
                                'bidder_id' => $bid->bidder_id,
                                'date_bid' => carbon($bid->date_bid),
                                'amount' => $bid->amount,
                            ]);
                        });

                        if (! $this->nextPage($response->getPagesCount()))
                            break;

                    } catch (RequestFailedException $e) {
                        if (strtolower($e->getError()) == 'contract not found') {
                            ContractDetail::where('contract_id', $this->contract_id)
                                ->update([
                                    'status' => 'deleted',
                                ]);

                            break;
                        }

                        throw $e;
                    }
                }
            }, function () {
                return $this->release(self::DELAY);
            });
    }
}
