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
use Seat\Eveapi\Mapping\Financial\WalletTransactionMapping;
use Seat\Eveapi\Models\Wallet\CorporationWalletBalance;
use Seat\Eveapi\Models\Wallet\CorporationWalletTransaction;

/**
 * Class Transactions.
 *
 * @package Seat\Eveapi\Jobs\Wallet\Corporation
 */
class Transactions extends AbstractAuthCorporationJob
{
    /**
     * @var string
     */
    protected $method = 'get';

    /**
     * @var string
     */
    protected $endpoint = '/corporations/{corporation_id}/wallets/{division}/transactions/';

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
     * A counter used to walk the transactions backwards.
     *
     * @var int
     */
    protected $from_id = 0;

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

        CorporationWalletBalance::where('corporation_id', $this->getCorporationId())->get()
            ->each(function ($balance) {

                // Perform a journal walk backwards to get all of the
                // entries as far back as possible. When the response from
                // ESI is empty, we can assume we have everything.
                while (true) {

                    $this->query_string = ['from_id' => $this->from_id];

                    $transactions = $this->retrieve([
                        'corporation_id' => $this->getCorporationId(),
                        'division'       => $balance->division,
                    ]);

                    if ($transactions->isCachedLoad() &&
                        CorporationWalletTransaction::where('corporation_id', $this->getCorporationId())->count() > 0)
                        return;

                    // If we have no more entries, break the loop.
                    if (collect($transactions)->count() === 0)
                        break;

                    collect($transactions)->each(function ($transaction) use ($balance) {

                        $transaction_entry = CorporationWalletTransaction::firstOrNew([
                            'corporation_id' => $this->getCorporationId(),
                            'division'       => $balance->division,
                            'transaction_id' => $transaction->transaction_id,
                        ]);

                        // If this transaction entry has already been recorded,
                        // move on to the next.
                        if ($transaction_entry->exists)
                            return;

                        WalletTransactionMapping::make($transaction_entry, $transaction, [
                            'corporation_id' => function () {
                                return $this->getCorporationId();
                            },
                            'division' => function () use ($balance) {
                                return $balance->division;
                            },
                        ])->save();
                    });

                    // Update the from_id to be the new lowest (ref_id - 1) that we
                    // know of. The next all will use this.
                    $this->from_id = collect($transactions)->min('transaction_id') - 1;
                }

                // Reset the from_id for the next wallet division
                $this->from_id = 0;
            });
    }
}
