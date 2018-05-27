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
use Seat\Eveapi\Models\Character\CharacterStanding;

/**
 * Class Standings.
 * @package Seat\Eveapi\Jobs\Character
 */
class Standings extends EsiBase
{
    /**
     * @var string
     */
    protected $method = 'get';

    /**
     * @var string
     */
    protected $endpoint = '/characters/{character_id}/standings/';

    /**
     * @var int
     */
    protected $version = 'v1';

    /**
     * @var string
     */
    protected $scope = 'esi-characters.read_standings.v1';

    /**
     * @var array
     */
    protected $tags = ['character', 'standings'];

    /**
     * Execute the job.
     *
     * @return void
     * @throws \Exception
     * @throws \Throwable
     */
    public function handle()
    {

        if (! $this->preflighted()) return;

        $standings = $this->retrieve([
            'character_id' => $this->getCharacterId(),
        ]);

        if ($standings->isCachedLoad()) return;

        collect($standings)->each(function ($standing) {

            CharacterStanding::firstOrNew([
                'character_id' => $this->getCharacterId(),
                'from_type'    => $standing->from_type,
                'from_id'      => $standing->from_id,
            ])->fill([
                'standing' => $standing->standing,
            ])->save();
        });
    }
}
