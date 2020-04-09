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

namespace Seat\Eveapi\Jobs\Character;

use Seat\Eveapi\Jobs\AbstractAuthCharacterJob;
use Seat\Eveapi\Models\Character\CharacterInfo;
use Seat\Eveapi\Models\Corporation\CorporationTitle;

/**
 * Class Title.
 * @package Seat\Eveapi\Jobs\Character
 */
class Titles extends AbstractAuthCharacterJob
{
    /**
     * @var string
     */
    protected $method = 'get';

    /**
     * @var string
     */
    protected $endpoint = '/characters/{character_id}/titles/';

    /**
     * @var int
     */
    protected $version = 'v1';

    /**
     * @var string
     */
    protected $scope = 'esi-characters.read_titles.v1';

    /**
     * @var array
     */
    protected $tags = ['character', 'role'];

    /**
     * @var \Illuminate\Support\Collection
     */
    private $active_titles;

    /**
     * Execute the job.
     *
     * @return void
     * @throws \Exception
     * @throws \Throwable
     */
    public function handle()
    {

        $titles = $this->retrieve([
            'character_id' => $this->getCharacterId(),
        ]);

        if ($titles->isCachedLoad()) return;

        $character = CharacterInfo::find($this->getCharacterId());

        if (is_null($character))
            return;

        $this->active_titles = collect();

        // Re-add the updated titles for this character
        collect($titles)->each(function ($title) {

            // retrieve or create title
            $corporation_title = CorporationTitle::firstOrCreate([
                'corporation_id' => $this->token->character->affiliation->corporation_id,
                'title_id'       => $title->title_id,
            ], [
                'name' => $title->name,
            ]);

            // seed titles buffer
            $this->active_titles->push($corporation_title->id);
        });

        // update character/titles relations
        $character->titles()->sync($this->active_titles->toArray());
    }
}
