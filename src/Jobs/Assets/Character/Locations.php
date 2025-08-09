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

namespace Seat\Eveapi\Jobs\Assets\Character;

use Seat\Eseye\Exceptions\RequestFailedException;
use Seat\Eveapi\Exception\InvalidAssetLocation;
use Seat\Eveapi\Jobs\AbstractAuthCharacterJob;
use Seat\Eveapi\Models\Assets\CharacterAsset;
use Seat\Eveapi\Traits\Utils;

/**
 * Class Locations.
 *
 * @package Seat\Eveapi\Jobs\Assets\Character
 */
class Locations extends AbstractAuthCharacterJob
{
    use Utils;

    /**
     * The maximum number of itemids we can request location
     * information for.
     *
     * @var int
     */
    const ITEMS_LIMIT = 1000;

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
    protected string $compatibility_date = '2025-07-20';

    /**
     * @var string
     */
    protected $scope = 'esi-assets.read_assets.v1';

    /**
     * @var array
     */
    protected $tags = ['character', 'asset'];

    /**
     * @var bool
     */
    private $has_exception = false;

    /**
     * Execute the job.
     *
     * @return void
     *
     * @throws \Exception
     */
    public function handle()
    {
        parent::handle();

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
            ->chunk(self::ITEMS_LIMIT, function ($item_ids) {

                $this->request_body = $item_ids->pluck('item_id')->all();

                try {
                    $response = $this->retrieve([
                        'character_id' => $this->getCharacterId(),
                    ]);

                    $locations = collect($response->getBody());

                    $locations->each(function ($location) {

                        $asset_data = CharacterAsset::where('character_id', $this->getCharacterId())
                            ->where('item_id', $location->item_id)
                            ->first();

                        // Location for items in either hangar or stations match to 0, 0, 0
                        $asset_data->fill([
                            'x' => $location->position->x,
                            'y' => $location->position->y,
                            'z' => $location->position->z,
                            'map_id' => 0,
                            'map_name' => '',
                        ]);

                        // If we have a non zero value for coordinates, attempt to identify a celestial.
                        if (($location->position->x + $location->position->y + $location->position->y) !== 0) {
                            $normalized_location = $this->find_nearest_celestial(
                                $asset_data->location_id,
                                $location->position->x, $location->position->y, $location->position->z);

                            // Update the assets location information
                            $asset_data->fill([
                                'map_id' => $normalized_location['map_id'],
                                'map_name' => $normalized_location['map_name'],
                            ]);
                        }

                        $asset_data->save();
                    });
                } catch (RequestFailedException $exception) {
                    $this->handleInvalidIdException($exception, $item_ids->pluck('item_id')->all());
                }
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
            ->chunk(self::ITEMS_LIMIT, function ($item_ids) {

                $this->request_body = $item_ids->pluck('item_id')->all();

                try {
                    $response = $this->retrieve([
                        'character_id' => $this->getCharacterId(),
                    ]);

                    $locations = collect($response->getBody());

                    $locations->each(function ($location) {

                        $asset_data = CharacterAsset::where('character_id', $this->getCharacterId())
                            ->where('item_id', $location->item_id)
                            ->first();

                        // Location for items in either hangar or stations match to 0, 0, 0
                        $asset_data->fill([
                            'x' => $location->position->x,
                            'y' => $location->position->y,
                            'z' => $location->position->z,
                            'map_id' => 0,
                            'map_name' => '',
                        ]);

                        // If we have a non zero value for coordinates, attempt to identify a celestial.
                        if (($location->position->x + $location->position->y + $location->position->y) !== 0) {
                            $normalized_location = $this->find_nearest_celestial(
                                $asset_data->location_id,
                                $location->position->x, $location->position->y, $location->position->z);

                            // Update the assets location information
                            $asset_data->fill([
                                'map_id' => $normalized_location['map_id'],
                                'map_name' => $normalized_location['map_name'],
                            ]);
                        }

                        $asset_data->save();
                    });
                } catch (RequestFailedException $exception) {
                    $this->handleInvalidIdException($exception, $item_ids->pluck('item_id')->all());
                }
            });

        if ($this->has_exception)
            throw new InvalidAssetLocation();
    }

    /**
     * @param  \Seat\Eseye\Exceptions\RequestFailedException  $exception
     * @param  array  $item_ids
     *
     * @throws \Seat\Eseye\Exceptions\RequestFailedException
     */
    private function handleInvalidIdException(RequestFailedException $exception, array $item_ids)
    {
        if ($exception->getError() !== 'Invalid IDs in the request')
            throw $exception;

        logger()->error(
            sprintf('[Jobs][%s] Request contains an invalid asset ID from which retrieve a location.', $this->job->getJobId()),
            [
                'character_id' => $this->character_id,
                'assets_batch' => $item_ids,
            ]);

        $this->has_exception = true;
    }
}
