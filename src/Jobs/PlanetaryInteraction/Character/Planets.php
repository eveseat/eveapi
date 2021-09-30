<?php

/*
 * This file is part of SeAT
 *
 * Copyright (C) 2015 to 2021 Leon Jacobs
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

namespace Seat\Eveapi\Jobs\PlanetaryInteraction\Character;

use Seat\Eveapi\Jobs\AbstractAuthCharacterJob;
use Seat\Eveapi\Models\PlanetaryInteraction\CharacterPlanet;

/**
 * Class Planet.
 *
 * @package Seat\Eveapi\Jobs\PlanetaryInteraction\Character
 */
class Planets extends AbstractAuthCharacterJob
{
    /**
     * @var string
     */
    protected $method = 'get';

    /**
     * @var string
     */
    protected $endpoint = '/characters/{character_id}/planets/';

    /**
     * @var int
     */
    protected $version = 'v1';

    /**
     * @var string
     */
    protected $scope = 'esi-planets.manage_planets.v1';

    /**
     * @var array
     */
    protected $tags = ['character', 'pi'];

    /**
     * Execute the job.
     *
     * @throws \Throwable
     */
    public function handle()
    {
        $planets = $this->retrieve([
            'character_id' => $this->getCharacterId(),
        ]);

        if (! $planets->isCachedLoad()) {

            collect($planets)->each(function ($planet) {

                CharacterPlanet::firstOrNew([
                    'character_id' => $this->getCharacterId(),
                    'solar_system_id' => $planet->solar_system_id,
                    'planet_id' => $planet->planet_id,
                ])->fill([
                    'upgrade_level' => $planet->upgrade_level,
                    'num_pins' => $planet->num_pins,
                    'last_update' => carbon($planet->last_update),
                    'planet_type' => $planet->planet_type,
                ])->save();

            });

            // Retrieve all waypoints which have not been returned by API.
            // We will run a delete statement on those selected rows in order to avoid any deadlock.
            $existing_planets = CharacterPlanet::where('character_id', $this->getCharacterId())
                ->whereNotIn('planet_id', collect($planets)->pluck('planet_id')->toArray())
                ->get();

            CharacterPlanet::where('character_id', $this->getCharacterId())
                ->whereIn('planet_id', $existing_planets->pluck('planet_id')->toArray())
                ->delete();
        }

        // for all planets, enqueue a job which will collect details
        CharacterPlanet::where('character_id', $this->getCharacterId())
            ->get()
            ->each(function ($planet) {
                PlanetDetail::dispatch($this->token, $planet->planet_id);
            });
    }
}
