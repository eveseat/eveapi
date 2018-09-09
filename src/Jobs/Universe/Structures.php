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

namespace Seat\Eveapi\Jobs\Universe;

use Seat\Eseye\Exceptions\RequestFailedException;
use Seat\Eveapi\Jobs\EsiBase;
use Seat\Eveapi\Models\Assets\CharacterAsset;
use Seat\Eveapi\Models\Assets\CorporationAsset;
use Seat\Eveapi\Models\Universe\UniverseStructure;
use Seat\Eveapi\Traits\RateLimitsCalls;

/**
 * Class Structures.
 * @package Seat\Eveapi\Jobs\Universe
 */
class Structures extends EsiBase
{
    use RateLimitsCalls;

    const BUGGED_ASSETS_RANGE = [40000000, 50000000];

    const ASSET_SAFETY = 2004;

    const SOLAR_SYSTEMS_RANGE = [30000000, 33000000];

    const UPWELL_STRUCTURES_RANGE = [60000000, 64000000];

    /**
     * The maximum number of calls that can be made per minute.
     * @var int
     */
    public $rate_limit = 20;

    /**
     * The cache key to use when checking the rate limit.
     *
     * @var string
     */
    public $rate_limit_key = 'universe.structures';

    /**
     * @var string
     */
    protected $method = 'get';

    /**
     * @var string
     */
    protected $endpoint = '/universe/structures/{structure_id}/';

    /**
     * @var string
     */
    protected $version = 'v2';

    /**
     * @var array
     */
    protected $tags = ['character', 'universe', 'structures'];

    /**
     * @var string
     */
    protected $scope = 'esi-universe.read_structures.v1';

    /**
     * Execute the job.
     *
     * @return void
     * @throws \Throwable
     * @throws \Psr\SimpleCache\InvalidArgumentException
     */
    public function handle()
    {

        if (! $this->preflighted()) return;

        // retrieve unresolved location from character
        $character_asset_locations = $this->getCharacterAssetLocations();

        // retrieve unresolved location from corporation
        $corporation_asset_locations = in_array('Director', $this->getCharacterRoles()) ?
            $this->getCorporationAssetLocations($character_asset_locations) : [];

        // merge both character and corporation arrays
        $location_ids = array_merge($character_asset_locations, $corporation_asset_locations);

        logger()->debug('Structure resolver', ['location_ids' => $location_ids]);

        foreach ($location_ids as $location_id) {

            try {

                // If we are rate limited, stop working.
                if ($this->isRateLimited()) break;

                $structure = $this->retrieve([
                    'structure_id' => $location_id,
                ]);

                // Increment the call count we have this far.
                $this->incrementRateLimitCallCount(1);

                UniverseStructure::firstOrNew([
                    'structure_id' => $location_id,
                ], [
                    'name'            => $structure->name,
                    'owner_id'        => $structure->owner_id,
                    'solar_system_id' => $structure->solar_system_id,
                    'x'               => $structure->position->x,
                    'y'               => $structure->position->y,
                    'z'               => $structure->position->z,
                    'type_id'         => $structure->type_id ?? null,
                ])->save();

            } catch (RequestFailedException $e) {

                // Failure to grab the structure should result in us creating an
                // empty entry in the database for this structure.

                $model = UniverseStructure::firstOrNew([
                    'structure_id' => $location_id,
                ])->fill([
                    'name'            => 'Unknown Structure',
                    'owner_id'        => null,
                    'solar_system_id' => 0,
                    'x'               => 0.0,
                    'y'               => 0.0,
                    'z'               => 0.0,
                    'type_id'         => null,
                ]);

                // persist the structure only if it doesn't already exist
                if (! $model->exists)
                    $model->save();
            }
        }
    }

    /**
     * Gets the current characters asset locations.
     *
     * @return array
     * @throws \Exception
     */
    private function getCharacterAssetLocations(): array
    {

        $assets = CharacterAsset::where('character_id', $this->getCharacterId())
            ->where('location_flag', 'Hangar')
            ->where('location_type', 'other')
            // Asset Safety
            ->where('location_id', '<>', self::ASSET_SAFETY)
            // Solar Systems
            ->whereNotBetween('location_id', self::SOLAR_SYSTEMS_RANGE)
            // Bugged Assets
            ->whereNotBetween('location_id', self::BUGGED_ASSETS_RANGE)
            // Station / Outpost
            ->whereNotBetween('location_id', self::UPWELL_STRUCTURES_RANGE)
            // stuffs
            ->whereNotIn('location_id', function ($query) {
                $query->select('item_id')
                    ->from((new CharacterAsset)->getTable())
                    ->where('character_id', $this->getCharacterId())
                    ->distinct();
            })
            // Remove structures that already have a name resolved
            // within the last week.
            ->whereNotIn('location_id', function ($query) {

                $query->select('structure_id')
                    ->from((new UniverseStructure)->getTable())
                    ->where('name', '<>', 'Unknown Structure')
                    ->where('updated_at', '<', carbon('now')->subWeek());
            })
            ->select('location_id')->distinct()
            // Until CCP can sort out this endpoint, pick 30 random locations
            // and try to get those names. We hard cap it at 30 otherwise we
            // will quickly kill the error limit, resulting in a ban.
            ->inRandomOrder()
            ->limit(15)
            ->get();

        return $assets->map(function ($asset) {

            return $asset->location_id;

        })->all();
    }

    /**
     * @param array $exclude_location_ids
     *
     * @return array
     * @throws \Exception
     */
    private function getCorporationAssetLocations(array $exclude_location_ids = []): array
    {

        $assets = CorporationAsset::where('corporation_id', $this->getCorporationId())
            ->whereIn('location_flag', ['OfficeFolder', 'CorpDeliveries'])
            ->where('location_type', 'other')
            // ignore already listed location_id
            ->whereNotIn('location_id', $exclude_location_ids)
            // Asset Safety
            ->where('location_id', '<>', self::ASSET_SAFETY)
            // Solar Systems
            ->whereNotBetween('location_id', self::SOLAR_SYSTEMS_RANGE)
            // Bugged Assets
            ->whereNotBetween('location_id', self::BUGGED_ASSETS_RANGE)
            // Station / Outpost
            ->whereNotBetween('location_id', self::UPWELL_STRUCTURES_RANGE)
            // stuffs
            ->whereNotIn('location_id', function ($query) {
                $query->select('item_id')
                    ->from((new CorporationAsset)->getTable())
                    ->where('corporation_id', $this->getCorporationId())
                    ->distinct();
            })
            // Remove structures that already have a name resolved
            // within the last week.
            ->whereNotIn('location_id', function ($query) {

                $query->select('structure_id')
                    ->from((new UniverseStructure)->getTable())
                    ->where('name', '<>', 'Unknown structure')
                    ->where('updated_at', '<', carbon('now')->subWeek());
            })
            ->select('location_id')
            ->distinct()
            // Until CCP can sort out this endpoint, pick 30 random locations
            // and try to get those names. We hard cap it at 30 otherwise we
            // will quickly kill the error limit, resulting in a ban.
            ->inRandomOrder()
            ->limit(15)
            ->get();

        return $assets->map(function ($asset) {

            return $asset->location_id;
        })->all();
    }
}
