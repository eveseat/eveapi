<?php

/*
 * This file is part of SeAT
 *
 * Copyright (C) 2015 to 2020 Leon Jacobs
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
use Seat\Eveapi\Jobs\AbstractAuthCharacterJob;
use Seat\Eveapi\Models\Assets\CharacterAsset;
use Seat\Eveapi\Models\Universe\UniverseStructure;

/**
 * Class CharacterStructures.
 *
 * @package Seat\Eveapi\Jobs\Universe
 */
class CharacterStructures extends AbstractAuthCharacterJob implements IStructures
{
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
    protected $scope = 'esi-universe.read_structures.v1';

    /**
     * @var string
     */
    protected $version = 'v2';

    /**
     * @var array
     */
    protected $tags = ['character', 'universe', 'structure'];

    /**
     * {@inheritdoc}
     */
    public function handle()
    {
        $structure_ids = $this->getStructuresIdToResolve();

        foreach ($structure_ids as $structure_id) {

            try {

                // attempt to resolve the structure
                $structure = $this->retrieve([
                    'structure_id' => $structure_id,
                ]);

                UniverseStructure::updateOrCreate([
                    'structure_id' => $structure_id,
                ], [
                    'name'            => $structure->name,
                    'owner_id'        => $structure->owner_id,
                    'solar_system_id' => $structure->solar_system_id,
                    'x'               => $structure->position->x,
                    'y'               => $structure->position->y,
                    'z'               => $structure->position->z,
                    'type_id'         => $structure->type_id ?? null,
                ]);

            } catch (RequestFailedException $e) {
                logger()->error('Unable to retrieve structure information.', [
                    'structure ID'   => $structure_id,
                    'token owner ID' => $this->getCharacterId(),
                ]);
            }
        }
    }

    /**
     * {@inheritdoc}
     */
    public function getStructuresIdToResolve(): array
    {
        return CharacterAsset::where('character_id', $this->getCharacterId())
            ->whereIn('location_flag', ['Deliveries', 'Hangar'])
            ->whereIn('location_type', ['item', 'other'])
            // according to ESI - structure ID has to start at a certain range
            ->where('location_id', '>=', self::START_UPWELL_RANGE)
            // exclude character assets
            ->whereNotIn('location_id', function ($query) {
                $query->select('item_id')
                    ->from((new CharacterAsset)->getTable())
                    ->where('character_id', $this->getCharacterId())
                    ->distinct();
            })
            ->select('location_id')
            ->distinct()
            // Until CCP can sort out this endpoint, pick 15 random locations
            // and try to get those names. We hard cap it at 15 otherwise we
            // will quickly kill the error limit, resulting in a ban.
            ->inRandomOrder()
            ->limit(15)
            ->get()
            ->pluck('location_id')
            ->all();
    }
}
