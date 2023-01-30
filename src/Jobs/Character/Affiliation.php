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

namespace Seat\Eveapi\Jobs\Character;

use Seat\Eveapi\Jobs\EsiBase;
use Seat\Eveapi\Models\Character\CharacterAffiliation;
use Seat\Eveapi\Models\Corporation\CorporationRole;

/**
 * Class Affiliation.
 *
 * @package Seat\Eveapi\Jobs\Character
 */
class Affiliation extends EsiBase
{
    /**
     * The maximum number of entities we can request affiliation information for.
     */
    const REQUEST_ID_LIMIT = 1000;

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
    protected $version = 'v2';

    /**
     * @var array
     */
    protected $tags = ['character'];

    /**
     * @var array
     */
    protected $character_ids;

    /**
     * Affiliation constructor.
     *
     * @param  array  $character_ids
     */
    public function __construct(array $character_ids)
    {
        $this->character_ids = $character_ids;
    }

    /**
     * Execute the job.
     *
     * @return void
     *
     * @throws \Exception
     */
    public function handle()
    {
        logger()->debug('Retrieving affiliation for the following characters list.', [
            'limit' => self::REQUEST_ID_LIMIT,
            'batch' => $this->character_ids,
        ]);

        collect($this->character_ids)->chunk(self::REQUEST_ID_LIMIT)->each(function ($chunk) {
            $this->request_body = $chunk->values()->all();
            $affiliations = $this->retrieve();

            collect($affiliations)->each(function ($affiliation) {

                //if a character changed corporation, remove his corporation roles
                $is_same_corp = CharacterAffiliation::where("character_id",$affiliation->character_id)
                    ->where("corporation_id",$affiliation->corporation_id)
                    ->exists();
                if(!$is_same_corp){
                    //make sure to update observers, so squads get updated
                    $roles = CorporationRole::where("character_id",$affiliation->character_id)->get();
                    foreach ($roles as $role){
                        $role->delete();
                    }
                }

                CharacterAffiliation::updateOrCreate(
                    ['character_id' => $affiliation->character_id],
                    ['corporation_id' => $affiliation->corporation_id, 'alliance_id' => $affiliation->alliance_id ?? null, 'faction_id' => $affiliation->faction_id ?? null]
                );
            });
        });
    }
}
