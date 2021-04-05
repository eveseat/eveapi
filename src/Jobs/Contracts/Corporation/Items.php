<?php

/*
 * This file is part of SeAT
 *
 * Copyright (C) 2015 to 2021 Leon Jacobs
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
use Seat\Eveapi\Exception\EmptyContractException;
use Seat\Eveapi\Exception\InvalidContractTypeException;
use Seat\Eveapi\Jobs\AbstractAuthCorporationJob;
use Seat\Eveapi\Models\Contracts\ContractDetail;
use Seat\Eveapi\Models\Contracts\ContractItem;
use Seat\Eveapi\Models\RefreshToken;

/**
 * Class Items.
 * @package Seat\Eveapi\Jobs\Contracts\Corporation
 */
class Items extends AbstractAuthCorporationJob
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
    protected $endpoint = '/corporations/{corporation_id}/contracts/{contract_id}/items/';

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
    public $tries = 60;

    /**
     * Items constructor.
     *
     * @param int $corporation_id
     * @param \Seat\Eveapi\Models\RefreshToken $token
     * @param int $contract_id
     */
    public function __construct(int $corporation_id, RefreshToken $token, int $contract_id)
    {
        $this->contract_id = $contract_id;

        array_push($this->tags, $contract_id);

        parent::__construct($corporation_id, $token);
    }

    /**
     * Execute the job.
     *
     * @return void
     * @throws \Throwable
     */
    public function handle()
    {
        $contract = ContractDetail::find($this->contract_id);

        if ($contract->type == 'courier')
            throw new InvalidContractTypeException();

        if ($contract->status == 'deleted')
            throw new DeletedContractException();

        if ($contract->volume <= 0)
            throw new EmptyContractException();

        if ($contract->lines->isNotEmpty())
            return;

         // The number of requests made in the current throttle cycle.
         // https://github.com/ccpgames/esi-issues/issues/636
         // > The way it works is you can make 20 requests per 10 seconds
         // > for a contract tied to a specific character ID.

        Redis::throttle(implode(':', ['corporations', $this->getCorporationId(), 'contracts']))
            ->allow(self::REQUESTS_LIMIT)
            ->every(self::DELAY)
            ->then(function () {

            try {
                $items = $this->retrieve([
                    'corporation_id' => $this->getCorporationId(),
                    'contract_id' => $this->contract_id,
                ]);

                if ($items->isCachedLoad() &&
                    ContractItem::where('contract_id', $this->contract_id)->count() > 0)
                    return;

                collect($items)->each(function ($item) {

                    ContractItem::updateOrCreate([
                        'record_id' => $item->record_id,
                    ], [
                        'contract_id' => $this->contract_id,
                        'type_id' => $item->type_id,
                        'quantity' => $item->quantity,
                        'raw_quantity' => $item->raw_quantity ?? null,
                        'is_singleton' => $item->is_singleton,
                        'is_included' => $item->is_included,
                    ]);
                });

            } catch (RequestFailedException $e) {
                if (strtolower($e->getError()) == 'contract not found!') {
                    ContractDetail::where('contract_id', $this->contract_id)
                        ->update([
                            'status' => 'deleted',
                        ]);

                    return;
                }

                throw $e;
            }
        }, function () {

            return $this->release(self::DELAY);
        });
    }
}
