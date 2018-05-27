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

namespace Seat\Eveapi\Jobs\Character;

use Seat\Eveapi\Jobs\EsiBase;
use Seat\Eveapi\Models\Character\CharacterAffiliation;
use Seat\Eveapi\Models\Character\CharacterNotification;
use Seat\Eveapi\Models\Contacts\CharacterContact;
use Seat\Eveapi\Models\Contracts\ContractDetail;
use Seat\Eveapi\Models\Mail\MailHeader;
use Seat\Eveapi\Models\Mail\MailRecipient;
use Seat\Eveapi\Models\RefreshToken;
use Seat\Eveapi\Models\Wallet\CharacterWalletJournal;
use Seat\Eveapi\Models\Wallet\CharacterWalletTransaction;

/**
 * Class Affiliation.
 * @package Seat\Eveapi\Jobs\Character
 */
class Affiliation extends EsiBase
{
    /**
     * @var string
     */
    protected $method = 'post';

    /**
     * @var string
     */
    protected $endpoint = '/characters/affiliation/';

    /**
     * @var int
     */
    protected $version = 'v1';

    /**
     * @var array
     */
    protected $tags = ['character', 'affiliations'];

    /**
     * The maximum number of itemids we can request affiliation
     * information for.
     *
     * @var int
     */
    protected $item_id_limit = 1000;

    /**
     * @var \Illuminate\Support\Collection
     */
    protected $character_ids;

    /**
     * Affiliation constructor.
     *
     * @param \Seat\Eveapi\Models\RefreshToken|null $token
     */
    public function __construct(RefreshToken $token = null)
    {

        $this->character_ids = collect();

        parent::__construct($token);
    }

    /**
     * Execute the job.
     *
     * @return void
     * @throws \Exception
     */
    public function handle()
    {

        if (! $this->preflighted()) return;

        // A list of column => query to retrieve character_ids for
        // affiliation lookup. If no constraint is needed to get
        // only character_ids, new instances of the model classes
        // are used.
        $queries = collect([
            'first_party_id'  => CharacterWalletJournal::join('universe_names',
                'first_party_id', '=', 'entity_id')->where('category', 'character')->select('first_party_id'),
            'second_party_id' => CharacterWalletJournal::join('universe_names',
                'second_party_id', '=', 'entity_id')->where('category', 'character')->select('second_party_id'),
            'client_id'       => CharacterWalletTransaction::whereBetween('client_id', [3000000, 4000000])
                ->orWhereBetween('client_id', [90000000, 98000000])
                ->select('client_id'),
            'contact_id'      => CharacterContact::where('contact_type', 'character'),
            'issuer_id'       => (new ContractDetail),
            'character_id'    => (new MailHeader),
            'from'            => MailHeader::whereBetween('from', [3000000, 4000000])
                ->orWhereBetween('from', [90000000, 98000000])
                ->select('from'),
            'recipient_id'    => MailRecipient::where('recipient_type', 'character'),
            'sender_id'       => CharacterNotification::where('sender_type', 'character'),
        ]);

        $queries->each(function ($query, $column) {

            // Add the results of the query to the current character_ids
            $this->character_ids->push($query->whereNotNull($column)->distinct()->pluck($column)->all());

            // Ensure the character_ids collection is flat and unique
            $this->character_ids = $this->character_ids->flatten()->unique();
        });

        // Perform the affiliation updates for all of the unique character_ids
        $this->character_ids->chunk($this->item_id_limit)->each(function ($chunk) {

            $this->request_body = $chunk->values()->all();
            $affiliations = $this->retrieve();

            collect($affiliations)->chunk(1000)->each(function ($affiliation_chunk) {

                $records = $affiliation_chunk->map(function ($affiliation) {
                    return [
                        'character_id'   => $affiliation->character_id,
                        'corporation_id' => $affiliation->corporation_id,
                        'alliance_id'    => $affiliation->alliance_id ?? null,
                        'faction_id'     => $affiliation->faction_id ?? null,
                        'created_at'     => carbon(),
                        'updated_at'     => carbon(),
                    ];
                });

                CharacterAffiliation::insertOnDuplicateKey($records->toArray(), [
                    'character_id',
                    'corporation_id',
                    'alliance_id',
                    'faction_id',
                    'updated_at',
                ]);
            });
        });
    }
}
