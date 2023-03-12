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

namespace Seat\Eveapi\Jobs\Universe\Structures;

use Seat\Eveapi\Models\RefreshToken;
use Seat\Eveapi\Models\Universe\UniverseStation;
use Seat\Eveapi\Models\Universe\UniverseStructure;

class StructureBatch
{
    const CITADEL_BATCH_SIZE = 100;
    const STATION_BATCH_SIZE = 100;
    const START_UPWELL_RANGE = 100000000;
    const RESOLVABLE_LOCATION_FLAGS = ['Hangar', 'Deliveries'];
    const RESOLVABLE_LOCATION_TYPES = ['item', 'other', 'station'];
    private array $citadels = [];
    private array $stations = [];

    public function addStructure($structure_id, $character_id)
    {
        if ($structure_id >= self::START_UPWELL_RANGE) {
            //by storing it in an array, we get rid of duplicates
            $this->citadels[$structure_id] = $character_id;
        } else {
            //by storing it in an array, we get rid of duplicates
            $this->stations[$structure_id] = $character_id;
        }
    }

    public function submitJobs()
    {
        // schedule citadels
        // group citadels by character
        $character_groups = collect($this->citadels)
            ->mapToGroups(function ($character, $citadel) {
                return [$character => $citadel];
            });
        foreach ($character_groups as $character => $citadels) {
            //only load unknown structures
            $citadels = $citadels->filter(function ($citadel) {
                return UniverseStructure::find($citadel) === null;
            });
            // if all citadels in this group are know, there is no need to load the citadel data
            if ($citadels->isEmpty()) continue;

            $token = RefreshToken::find($character);
            if (! $token) continue;

            Citadels::dispatch($citadels, $token);
        }

        //stations
        collect($this->stations)
            ->keys()
            ->filter(function ($station_id) {
                return ! UniverseStation::where('station_id', $station_id)->exists();
            })
            ->chunk(self::STATION_BATCH_SIZE)
            ->each(function ($batch) {
                Stations::dispatch($batch);
            });
    }
}
