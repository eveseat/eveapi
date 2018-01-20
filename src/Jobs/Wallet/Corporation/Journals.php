<?php

/*
 * This file is part of SeAT
 *
 * Copyright (C) 2015, 2016, 2017  Leon Jacobs
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
use Seat\Eveapi\Models\Wallet\CorporationWalletJournalExtraInfo;

/**
 * Class Journals
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
    protected $version = 'v2';

    /**
     * A counter used to walk the journal backwards.
     *
     * @var int
     */
    protected $from_id = PHP_INT_MAX;

    /**
     * Execute the job.
     *
     * @return void
     * @throws \Throwable
     */
    public function handle()
    {

        CorporationDivision::where('corporation_id', $this->getCorporationId())->get()
            ->each(function ($division) {

                // Perform a journal walk backwards to get all of the
                // entries as far back as possible. When the response from
                // ESI is empty, we can assume we have everything.
                while (true) {

                    $this->query_string = ['from_id' => $this->from_id];

                    $journal = $this->retrieve([
                        'corporation_id' => $this->getCorporationId(),
                        'division'       => $division->division,
                    ]);

                    // If we have no more entries, break the loop.
                    if (collect($journal)->count() === 0)
                        break;

                    collect($journal)->each(function ($entry) use ($division) {

                        $journal_entry = CorporationWalletJournal::firstOrNew([
                            'corporation_id' => $this->getCorporationId(),
                            'division'       => $division->division,
                            'ref_id'         => $entry->ref_id,
                        ]);

                        // If this journal entry has already been recorded,
                        // move on to the next.
                        if ($journal_entry->exists)
                            return;

                        $journal_entry->fill([
                            'corporation_id'    => $this->getCorporationId(),
                            'division'          => $division->division,
                            'ref_id'            => $entry->ref_id,
                            'date'              => carbon($entry->date),
                            'ref_type'          => $entry->ref_type,
                            'first_party_id'    => $entry->first_party_id ?? null,
                            'first_party_type'  => $entry->first_party_type ?? null,
                            'second_party_id'   => $entry->second_party_id ?? null,
                            'second_party_type' => $entry->second_party_type ?? null,
                            'amount'            => $entry->amount ?? null,
                            'balance'           => $entry->balance ?? null,
                            'reason'            => $entry->reason ?? null,
                            'tax_receiver_id'   => $entry->tax_receiver_id ?? null,
                            'tax'               => $entry->tax ?? null,
                        ])->save();

                        // Process the 'extra' information for this journal entry.
                        // If no 'extra' information exists then we can bail here
                        // and move on to the next entry.
                        if (is_null($entry->extra_info ?? null) || collect($entry->extra_info)
                                ->count() === 0)
                            return;

                        $extra_info = CorporationWalletJournalExtraInfo::firstOrNew([
                            'ref_id' => $entry->ref_id,
                        ]);

                        // If we have already recorded the extra info for this journal
                        // reference id then bail.
                        if ($extra_info->exists)
                            return;

                        $extra_info->fill([
                            'location_id'            => $entry->extra_info->location_id ?? null,
                            'transaction_id'         => $entry->extra_info->transaction_id ?? null,
                            'npc_name'               => $entry->extra_info->npc_name ?? null,
                            'npc_id'                 => $entry->extra_info->npc_id ?? null,
                            'destroyed_ship_type_id' => $entry->extra_info->destroyed_ship_type_id ?? null,
                            'character_id'           => $entry->extra_info->character_id ?? null,
                            'corporation_id'         => $entry->extra_info->corporation_id ?? null,
                            'alliance_id'            => $entry->extra_info->alliance_id ?? null,
                            'job_id'                 => $entry->extra_info->job_id ?? null,
                            'contract_id'            => $entry->extra_info->contract_id ?? null,
                            'system_id'              => $entry->extra_info->system_id ?? null,
                            'planet_id'              => $entry->extra_info->planet_id ?? null,
                        ])->save();

                    });

                    // Update the from_id to be the new lowest ref_id we
                    // know of. The next call will use this.
                    $this->from_id = collect($journal)->min('ref_id') - 1;
                }

                // Reset the from_id for the next wallet division
                $this->from_id = PHP_INT_MAX;
            });
    }
}
