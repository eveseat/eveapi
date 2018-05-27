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
    protected $version = 'v4';

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
     * A counter used to walk the journal backwards.
     *
     * @var int
     */
    protected $from_id = PHP_INT_MAX;

    /**
     * Execute the job.
     *
     * @throws \Throwable
     */
    public function handle()
    {

        if (! $this->preflighted()) return;

        // Perform a journal walk backwards to get all of the
        // entries as far back as possible. When the response from
        // ESI is empty, we can assume we have everything.
        while (true) {

            $this->query_string = ['from_id' => $this->from_id];

            $journal = $this->retrieve([
                'character_id' => $this->getCharacterId(),
            ]);

            if ($journal->isCachedLoad()) return;

            // If we have no more entries, break the loop.
            if (collect($journal)->count() === 0)
                break;

            collect($journal)->each(function ($entry) {

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

            // Update the from_id to be the new lowest ref_id we
            // know of. The next all will use this.
            $this->from_id = collect($journal)->min('id') - 1;

            if (! $this->nextPage($journal->pages))
                break;
        }
    }
}
