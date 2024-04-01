<?php

/*
 * This file is part of SeAT
 *
 * Copyright (C) 2015 to present Leon Jacobs
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

                return                                                                          // only schedule the citadel if:
                    UniverseStructure::find($citadel_id) === null                               // we don't already know it
                    && CacheCitadelAccessCache::canAccess($token->character_id, $citadel_id)    // the character isn't banned
                    && !$this->isCurrentlyProcessing($citadel_id, $token->character_id);        // we haven't already scheduled it
            });

            foreach ($citadels as $citadel_id) {
                // mark this character-citadel combination as already in progress
                $this->setStructureCurrentlyProcessing($citadel_id, $token->character_id);
                // schedule the job
                $jobs->add(new Citadels($citadel_id, $token));
            }
        }

        if($jobs->isEmpty()) return;

        // enqueue batch
        Bus::batch($jobs->toArray())
            ->name('Structures')
            ->dispatch();
    }

    /**
     * Returns whether a job for this citadel has already been scheduled.
     * This logic doesn't need to be 100% race-condition proof, as soon as it catches 99% it does its job
     * @param int $structure_id
     * @param int $character_id
     * @return bool
     */
    private function isCurrentlyProcessing(int $structure_id, int $character_id): bool {
        return cache()->get(sprintf('structure.%d.processing.%d', $structure_id, $character_id), false);
    }

    /**
     * Set a structure as already processing.
     * This logic doesn't need to be 100% race-condition proof, as soon as it catches 99% it does its job
     * @param int $structure_id
     * @param int $character_id
     * @return void
     */
    private function setStructureCurrentlyProcessing(int $structure_id, int $character_id): void {
        cache()->set(sprintf('structure.%d.processing.%d', $structure_id, $character_id),true,now()->addMinutes(60));
    }
}
