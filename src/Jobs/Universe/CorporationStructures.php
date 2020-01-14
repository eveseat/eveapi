<?php

/*
 * This file is part of SeAT
 *
 * Copyright (C) 2015, 2016, 2017, 2018, 2019  Leon Jacobs
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
use Seat\Eveapi\Jobs\AbstractAuthCorporationJob;
use Seat\Eveapi\Models\Assets\CorporationAsset;
use Seat\Eveapi\Models\Corporation\CorporationStructure;
use Seat\Eveapi\Models\Universe\UniverseStructure;

/**
 * Class CorporationStructures.
 *
 * @package Seat\Eveapi\Jobs\Universe
 */
class CorporationStructures extends AbstractAuthCorporationJob implements IStructures
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
     * @var array
     */
    protected $roles = ['Director'];

    /**
     * @var string
     */
    protected $version = 'v2';

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
                    'token owner ID' => $this->token->character_id,
                    'corporation ID' => $this->getCorporationId(),
                ]);
            }
        }
    }

    /**
     * {@inheritdoc}
     */
    public function getStructuresIdToResolve(): array
    {
        $assets = CorporationAsset::where('corporation_id', $this->getCorporationId())
            ->where('location_flag', 'Hangar')
            ->where('location_type', 'other')
            // according to ESI - structure ID has to start at a certain range
            ->where('location_id', '>=', self::START_UPWELL_RANGE)
            // exclude assets safety which are in a doom area
            ->where('location_id', '<>', self::ASSET_SAFETY)
            // exclude solar system
            ->whereNotBetween('location_id', self::SOLAR_SYSTEMS_RANGE)
            // exclude bugged assets area
            ->whereNotBetween('location_id', self::BUGGED_ASSETS_RANGE)
            // exclude npc stations
            ->whereNotBetween('location_id', self::NPC_STATIONS_RANGE)
            // exclude character assets
            ->whereNotIn('location_id', function ($query) {
                $query->select('item_id')
                    ->from((new CorporationAsset)->getTable())
                    ->where('corporation_id', $this->getCorporationId())
                    ->distinct();
            })
            // exclude already resolved structures
            ->whereNotIn('location_id', function ($query) {
                $query->select('structure_id')
                    ->from((new UniverseStructure)->getTable());
            })
            ->select('location_id')
            ->distinct()
            // Until CCP can sort out this endpoint, pick 30 random locations
            // and try to get those names. We hard cap it at 30 otherwise we
            // will quickly kill the error limit, resulting in a ban.
            ->inRandomOrder()
            ->limit(15)
            ->get()
            ->values()
            ->all();

        $structures = CorporationStructure::where('corporation_id', $this->getCorporationId())
            ->whereNotIn('structure_id', function ($query) {
                $query->select('structure_id')
                    ->from((new UniverseStructure)->getTable());
            })
            ->select('structure_id')
            ->get()
            ->values()
            ->all();

        return array_merge($assets, $structures);
    }
}
