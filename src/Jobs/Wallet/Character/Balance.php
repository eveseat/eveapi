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

namespace Seat\Eveapi\Jobs\Wallet\Character;

use Seat\Eveapi\Jobs\AbstractAuthCharacterJob;
use Seat\Eveapi\Models\Wallet\CharacterWalletBalance;

/**
 * Class Balance.
 *
 * @package Seat\Eveapi\Jobs\Wallet\Character
 */
class Balance extends AbstractAuthCharacterJob
{
    /**
     * @var string
     */
    protected $method = 'get';

    /**
     * @var string
     */
    protected $endpoint = '/characters/{character_id}/wallet/';

    /**
     * @var string
     */
    protected string $compatibility_date = '2025-07-20';

    /**
     * @var string
     */
    protected $scope = 'esi-wallet.read_character_wallet.v1';

    /**
     * @var array
     */
    protected $tags = ['character', 'wallet'];

    /**
     * Execute the job.
     *
     * @throws \Throwable
     */
    public function handle()
    {
        parent::handle();

        $response = $this->retrieve([
            'character_id' => $this->getCharacterId(),
        ]);

        $balance = $response->getBody();

        CharacterWalletBalance::firstOrNew([
            'character_id' => $this->getCharacterId(),
        ])->fill([
            'balance' => $balance->scalar,
        ])->save();
    }
}
