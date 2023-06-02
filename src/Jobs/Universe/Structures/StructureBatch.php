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

use Illuminate\Support\Facades\Bus;
use Seat\Eveapi\Models\RefreshToken;
use Seat\Eveapi\Models\Universe\UniverseStation;
use Seat\Eveapi\Models\Universe\UniverseStructure;

class StructureBatch
{
    const START_CITADEL_RANGE = 100000000;
    const RESOLVABLE_LOCATION_FLAGS = ['Hangar', 'Deliveries', 'OfficeFolder'];
    const RESOLVABLE_LOCATION_TYPES = ['item', 'other', 'station'];

    private $structures = [];

    public function addStructure($structure_id)
    {
        // use an array to filter out duplicates
        $this->structures[$structure_id] = true;
    }

    public function submitJobs(?RefreshToken $token = null)
    {
        // sort by whether it is a citadel or station
        [$stations, $citadels] = collect($this->structures)
            ->keys()
            ->partition(function (int $id) {
                return $id < self::START_CITADEL_RANGE;
            });

        // only schedule the batch if there are actual structures to load. Therefore, we don't directly schedule them and instead store them in a list
        $jobs = collect();

        //filter out already known stations, schedule the rest
        $stations = $stations->filter(function ($station_id) {
            return UniverseStation::find($station_id) === null;
        });
        if($stations->isNotEmpty()){
            $jobs->add(new Stations($stations));
        }

        // we can only load citadels if we have a token
        if($token !== null) {
            // only dispatch the job if the citadel is unknown AND the character is not acl banned
            $citadels = $citadels->filter(function ($citadel_id) use ($token) {
                return UniverseStructure::find($citadel_id) === null && CacheCitadelAccessCache::canAccess($token->character_id, $citadel_id);
            });
            if ($citadels->isNotEmpty()) {
                $jobs->add(new Citadels($citadels, $token));
            }
        }

        if($jobs->isEmpty()) return;

        // enqueue batch
        Bus::batch($jobs->toArray())
            ->name('Structures')
            ->dispatch();
    }
}
