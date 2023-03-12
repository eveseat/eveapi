<?php

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
            if (!$token) continue;

            Citadels::dispatch($citadels, $token);
        }

        //stations
        collect($this->stations)
            ->keys()
            ->filter(function ($station_id) {
                return !UniverseStation::where('station_id', $station_id)->exists();
            })
            ->chunk(self::STATION_BATCH_SIZE)
            ->each(function ($batch) {
                Stations::dispatch($batch);
            });
    }
}