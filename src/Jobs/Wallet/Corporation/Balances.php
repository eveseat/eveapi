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

namespace Seat\Eveapi\Jobs\Wallet\Corporation;

use Seat\Eveapi\Jobs\AbstractAuthCorporationJob;
use Seat\Eveapi\Models\Wallet\CorporationWalletBalance;

/**
 * Class Balances.
 *
 * @package Seat\Eveapi\Jobs\Wallet\Corporation
 */
class Balances extends AbstractAuthCorporationJob
{
    /**
     * @var string
     */
    protected $method = 'get';

    /**
     * @var string
     */
    protected $endpoint = '/corporations/{corporation_id}/wallets/';

    /**
     * @var string
     */
    protected $version = 'v1';

    /**
     * @var string
     */
    protected $scope = 'esi-wallet.read_corporation_wallets.v1';

    /**
     * @var array
     */
    protected $roles = ['Accountant', 'Junior_Accountant'];

    /**
     * @var array
     */
    protected $tags = ['corporation', 'wallet'];

    /**
     * @return string
     */
    public function displayName(): string
    {
        return "Retrieve corporation wallet balances";
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
        parent::handle();

        $response = $this->retrieve([
            'corporation_id' => $this->getCorporationId(),
        ]);

        if ($response->isFromCache() &&
            CorporationWalletBalance::where('corporation_id', $this->getCorporationId())->count() > 0)
            return;

        $balances = $response->getBody();

        collect($balances)->each(function ($balance) {

            CorporationWalletBalance::firstOrNew([
                'corporation_id' => $this->getCorporationId(),
                'division'       => $balance->division,
            ])->fill([
                'balance' => $balance->balance,
            ])->save();

        });
    }
}
