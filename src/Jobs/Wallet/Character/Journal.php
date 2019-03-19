<?php

/*
 * This file is part of SeAT
 *
 * Copyright (C) 2015, 2016, 2017, 2018  Leon Jacobs
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

use Seat\Eveapi\Jobs\EsiBase;
use Seat\Eveapi\Models\Wallet\CharacterWalletJournal;

/**
 * Class Journal.
 * @package Seat\Eveapi\Jobs\Wallet\Character
 */
class Journal extends EsiBase
{
    /**
     * @var string
     */
    protected $method = 'get';

    /**
     * @var string
     */
    protected $endpoint = '/characters/{character_id}/wallet/journal/';

    /**
     * @var string
     */
    protected $version = 'v5';

    /**
     * @var string
     */
    protected $scope = 'esi-wallet.read_character_wallet.v1';

    /**
     * @var array
     */
    protected $tags = ['character', 'wallet', 'journal'];

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

        // retrieve latest known journal entry for the active character.
        $last_known_entry = CharacterWalletJournal::where('character_id', $this->getCharacterId())
                                                  ->orderBy('date', 'desc')
                                                  ->first();

        if (! is_null($last_known_entry))
            $this->last_known_entry_id = $last_known_entry->id;

        // Perform a journal walk backwards to get all of the
        // entries as far back as possible. When the response from
        // ESI is empty, we can assume we have everything.
        while (true) {

            $journal = $this->retrieve([
                'character_id' => $this->getCharacterId(),
            ]);

            if ($journal->isCachedLoad()) return;

            $entries = collect($journal);

            // If we have no more entries, break the loop.
            if ($entries->count() === 0)
                break;

            $entries->each(function ($entry) {

                // if we've reached the last known entry - abort the process
                if ($entry->id == $this->last_known_entry_id) {
                    $this->reach_last_known_entry = true;
                    return false;
                }

                $journal_entry = CharacterWalletJournal::firstOrNew([
                    'character_id' => $this->getCharacterId(),
                    'id'           => $entry->id,
                ]);

                // If this journal entry has already been recorded,
                // move on to the next.
                if ($journal_entry->exists)
                    return;

                $journal_entry->fill([
                    'character_id'    => $this->getCharacterId(),
                    'id'              => $entry->id,                         // changed from ref_id to id into v4
                    'date'            => carbon($entry->date),
                    'ref_type'        => $entry->ref_type,
                    'first_party_id'  => $entry->first_party_id ?? null,
                    'second_party_id' => $entry->second_party_id ?? null,
                    'amount'          => $entry->amount ?? null,
                    'balance'         => $entry->balance ?? null,
                    'reason'          => $entry->reason ?? null,
                    'tax_receiver_id' => $entry->tax_receiver_id ?? null,
                    'tax'             => $entry->tax ?? null,
                    // appears in version 4
                    'description'     => $entry->description,
                    'context_id'      => $entry->context_id ?? null,
                    'context_id_type' => $entry->context_id_type ?? null,
                ])->save();

            });

            // in case the last known entry has been reached or we non longer have pages, terminate the job.
            if (! $this->nextPage($journal->pages) || $this->reach_last_known_entry)
                break;
        }
    }
}
