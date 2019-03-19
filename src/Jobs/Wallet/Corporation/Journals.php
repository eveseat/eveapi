<?php

/*
 * This file is part of SeAT
 *
 * Copyright (C) 2015, 2016, 2017, 2018, 2019  Leon Jacobs
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

use Seat\Eveapi\Jobs\EsiBase;
use Seat\Eveapi\Models\Corporation\CorporationDivision;
use Seat\Eveapi\Models\Wallet\CorporationWalletJournal;

/**
 * Class Journals.
 * @package Seat\Eveapi\Jobs\Wallet\Corporation
 */
class Journals extends EsiBase
{
    /**
     * @var string
     */
    protected $method = 'get';

    /**
     * @var string
     */
    protected $endpoint = '/corporations/{corporation_id}/wallets/{division}/journal/';

    /**
     * @var string
     */
    protected $version = 'v4';

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
    protected $tags = ['corporation', 'wallet', 'journals'];

    /**
     * @var int
     */
    protected $page = 1;

    /**
     * @var int
     */
    protected $last_known_entry_id = 0;

    /**
     * @var bool
     */
    protected $reach_last_known_entry = false;

    /**
     * Execute the job.
     *
     * @throws \Throwable
     */
    public function handle()
    {

        if (! $this->preflighted()) return;

        CorporationDivision::where('corporation_id', $this->getCorporationId())->get()
            ->each(function ($division) {

                // retrieve last known entry for the current division and active corporation
                $last_known_entry = CorporationWalletJournal::where('corporation_id', $this->getCorporationId())
                                                            ->where('division', $division->division)
                                                            ->orderBy('date', 'desc')
                                                            ->first();

                if (! is_null($last_known_entry))
                    $this->last_known_entry_id = $last_known_entry->id;

                // Perform a journal walk backwards to get all of the
                // entries as far back as possible. When the response from
                // ESI is empty, we can assume we have everything.
                while (true) {

                    $journal = $this->retrieve([
                        'corporation_id' => $this->getCorporationId(),
                        'division'       => $division->division,
                    ]);

                    if ($journal->isCachedLoad()) return;

                    $entries = collect($journal);

                    // If we have no more entries, break the loop.
                    if ($entries->count() === 0)
                        break;

                    $entries->chunk(1000)->each(function ($chunk) use ($division) {

                        // if we've reached the last known entry - abort the process
                        if ($this->reach_last_known_entry)
                            return false;

                        // if we have reached the last known entry, exclude all entries which are lower or equal to the
                        // last known entry and flag the reached status.
                        if ($chunk->where('id', $this->last_known_entry_id)->isNotEmpty()) {
                            $chunk = $chunk->where('id', '>', $this->last_known_entry_id);

                            $this->reach_last_known_entry = true;
                        }

                        $records = $chunk->map(function ($entry, $key) use ($division) {

                            return [
                                'corporation_id'  => $this->getCorporationId(),
                                'division'        => $division->division,
                                'id'              => $entry->id,
                                'date'            => carbon($entry->date),
                                'ref_type'        => $entry->ref_type,
                                'first_party_id'  => $entry->first_party_id ?? null,
                                'second_party_id' => $entry->second_party_id ?? null,
                                'amount'          => $entry->amount ?? null,
                                'balance'         => $entry->balance ?? null,
                                'reason'          => $entry->reason ?? null,
                                'tax_receiver_id' => $entry->tax_receiver_id ?? null,
                                'tax'             => $entry->tax ?? null,
                                // introduced in v4
                                'description'     => $entry->description,
                                'context_id'      => $entry->context_id ?? null,
                                'context_id_type' => $entry->context_id_type ?? null,
                                'created_at'      => carbon(),
                                'updated_at'      => carbon(),
                            ];
                        });

                        CorporationWalletJournal::insertIgnore($records->toArray());
                    });

                    // in case the last known entry has been reached or we non longer have pages, terminate the job.
                    if (! $this->nextPage($journal->pages) || $this->reach_last_known_entry)
                        break;
                }

                // Reset the page for the next wallet division.
                $this->page = 1;

                // Reset the last known entry for the next wallet division.
                $this->last_known_entry_id = 0;

                // Reset the last known entry status for the next wallet division.
                $this->reach_last_known_entry = false;
            });
    }
}
