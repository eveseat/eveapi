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

namespace Seat\Eveapi\Jobs\Clones;

use Seat\Eveapi\Jobs\EsiBase;
use Seat\Eveapi\Models\Clones\CharacterClone;
use Seat\Eveapi\Models\Clones\CharacterJumpClone;

/**
 * Class Clones.
 * @package Seat\Eveapi\Jobs\Clones
 */
class Clones extends EsiBase
{
    /**
     * @var string
     */
    protected $method = 'get';

    /**
     * @var string
     */
    protected $endpoint = '/characters/{character_id}/clones/';

    /**
     * @var string
     */
    protected $version = 'v3';

    /**
     * @var string
     */
    protected $scope = 'esi-clones.read_clones.v1';

    /**
     * @var array
     */
    protected $tags = ['character', 'clones'];

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

        $clone = $this->retrieve([
            'character_id' => $this->getCharacterId(),
        ]);

        if ($clone->isCachedLoad()) return;

        // Populate current clone information
        CharacterClone::firstOrNew([
            'character_id' => $this->getCharacterId(),
        ])->fill([
            'last_clone_jump_date'     => isset($clone->last_clone_jump_date) ?
                carbon($clone->last_clone_jump_date) : null,
            'home_location_id'         => isset($clone->home_location) ?
                $clone->home_location->location_id : null,
            'home_location_type'       => isset($clone->home_location) ?
                $clone->home_location->location_type : null,
            'last_station_change_date' => isset($clone->last_station_change_date) ?
                carbon($clone->last_station_change_date) : null,
        ])->save();

        // Populate jump clone information
        collect($clone->jump_clones)->each(function ($jump_clone) {

            CharacterJumpClone::firstOrNew([
                'character_id'  => $this->getCharacterId(),
                'jump_clone_id' => $jump_clone->jump_clone_id,
            ])->fill([
                'name'          => isset($jump_clone->name) ? $jump_clone->name : null,
                'location_id'   => $jump_clone->location_id,
                'location_type' => $jump_clone->location_type,
                'implants'      => json_encode($jump_clone->implants),
            ])->save();
        });

        // Remove invalid jump clones
        CharacterJumpClone::where('character_id', $this->getCharacterId())
            ->whereNotIn('jump_clone_id', collect($clone->jump_clones)
                ->pluck('jump_clone_id')->flatten()->all())
            ->delete();
    }
}
