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

namespace Seat\Eveapi\Jobs\Contracts\Character;

use Seat\Eseye\Exceptions\RequestFailedException;
use Seat\Eveapi\Jobs\AbstractAuthCharacterJob;
use Seat\Eveapi\Models\Contracts\CharacterContract;
use Seat\Eveapi\Models\Contracts\ContractBid;
use Seat\Eveapi\Models\Contracts\ContractDetail;

/**
 * Class Bids.
 * @package Seat\Eveapi\Jobs\Contracts\Character
 */
class Bids extends AbstractAuthCharacterJob
{
    /**
     * @var string
     */
    protected $method = 'get';

    /**
     * @var string
     */
    protected $endpoint = '/characters/{character_id}/contracts/{contract_id}/bids/';

    /**
     * @var string
     */
    protected $version = 'v1';

    /**
     * @var string
     */
    protected $scope = 'esi-contracts.read_character_contracts.v1';

    /**
     * @var array
     */
    protected $tags = ['contracts', 'bids'];

    /**
     * Execute the job.
     *
     * @return void
     * @throws \Exception
     */
    public function handle()
    {

        if (! $this->preflighted()) return;

        $unfinished_auctions = CharacterContract::join('contract_details',
            'character_contracts.contract_id', '=',
            'contract_details.contract_id')
            ->where('character_id', $this->getCharacterId())
            ->where('type', 'auction')
            ->whereNotIn('status', ['finished', 'deleted'])
            ->pluck('character_contracts.contract_id');

        $unfinished_auctions->each(function ($contract_id) {

            try {
                $bids = $this->retrieve([
                    'character_id' => $this->getCharacterId(),
                    'contract_id' => $contract_id,
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
            } catch (RequestFailedException $e) {
                if (strtolower($e->getError()) == 'contract not found') {
                    ContractDetail::where('contract_id', $contract_id)
                        ->update([
                            'status' => 'finished',
                        ]);

                    return;
                }

                throw $e;
            }
        });
    }
}
