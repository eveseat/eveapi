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

namespace Seat\Eveapi\Jobs\Assets\Character;

use Seat\Eveapi\Jobs\EsiBase;
use Seat\Eveapi\Models\Assets\CharacterAsset;
use Seat\Eveapi\Traits\Utils;

/**
 * Class Locations.
 * @package Seat\Eveapi\Jobs\Assets\Character
 */
class Locations extends EsiBase
{
    use Utils;

    /**
     * @var string
     */
    protected $method = 'post';

    /**
     * @var string
     */
    protected $endpoint = '/characters/{character_id}/assets/locations/';

    /**
     * @var string
     */
    protected $version = 'v2';

    /**
     * @var string
     */
    protected $scope = 'esi-assets.read_assets.v1';

    /**
     * @var array
     */
    protected $tags = ['character', 'assets', 'locations'];

    /**
     * The maximum number of itemids we can request location
     * information for.
     *
     * @var int
     */
    protected $item_id_limit = 1000;

    /**
     * Execute the job.
     *
     * @return void
     * @throws \Exception
     */
    public function handle()
    {

        if (! $this->authenticated()) return;

        // Get the assets for this character, chunked in a number of blocks
        // that the endpoint will accept.
        CharacterAsset::join('invTypes', 'type_id', '=', 'typeID')
            ->join('invGroups', 'invGroups.groupID', '=', 'invTypes.groupID')
            ->where('character_id', $this->getCharacterId())
            ->where('is_singleton', true)// only singleton items may have a specific location
            // It seems like only items from these categories can have a specific location
            // 2  : Celestial
            // 6  : Ship
            // 22 : Deployable
            // 23 : Starbase
            // 65 : Structure
            ->whereIn('categoryID', [2, 6, 22, 23, 65])
            ->select('item_id')
            ->chunk($this->item_id_limit, function ($item_ids) {

                $this->request_body = $item_ids->pluck('item_id')->all();

                $locations = $this->retrieve([
                    'character_id' => $this->getCharacterId(),
                ]);

                collect($locations)->each(function ($location) {

                    // If we have a zero value for any of the coordinates,
                    // continue to the next location as we can't calculate
                    // anything
                    if ($location->position->x === 0.0)
                        return;

                    $asset_data = CharacterAsset::where('character_id', $this->getCharacterId())
                        ->where('item_id', $location->item_id)
                        ->first();

                    $normalized_location = $this->find_nearest_celestial(
                        $asset_data->location_id,
                        $location->position->x, $location->position->y, $location->position->z);

                    // Update the assets location information
                    $asset_data->fill([
                        'x'        => $location->position->x,
                        'y'        => $location->position->y,
                        'z'        => $location->position->z,
                        'map_id'   => $normalized_location['map_id'],
                        'map_name' => $normalized_location['map_name'],
                    ])->save();
                });
            });

        // items which may not be singleton

        // Get the assets for this character, chunked in a number of blocks
        // that the endpoint will accept.
        CharacterAsset::join('invTypes', 'type_id', '=', 'typeID')
            ->join('invGroups', 'invGroups.groupID', '=', 'invTypes.groupID')
            ->where('character_id', $this->getCharacterId())
            // it seems only items from that categories can have a specific location
            // 46 : Orbitals
            ->whereIn('categoryID', [46])
            ->select('item_id')
            ->chunk($this->item_id_limit, function ($item_ids) {

                $this->request_body = $item_ids->pluck('item_id')->all();

                $locations = $this->retrieve([
                    'character_id' => $this->getCharacterId(),
                ]);

                collect($locations)->each(function ($location) {

                    // If we have a zero value for any of the coordinates,
                    // continue to the next location as we can't calculate
                    // anything
                    if ($location->position->x === 0.0)
                        return;

                    $asset_data = CharacterAsset::where('character_id', $this->getCharacterId())
                        ->where('item_id', $location->item_id)
                        ->first();

                    $normalized_location = $this->find_nearest_celestial(
                        $asset_data->location_id,
                        $location->position->x, $location->position->y, $location->position->z);

                    // Update the assets location information
                    $asset_data->fill([
                        'x'        => $location->position->x,
                        'y'        => $location->position->y,
                        'z'        => $location->position->z,
                        'map_id'   => $normalized_location['map_id'],
                        'map_name' => $normalized_location['map_name'],
                    ])->save();
                });
            });
    }
}
