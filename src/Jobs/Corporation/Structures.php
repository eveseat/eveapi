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

namespace Seat\Eveapi\Jobs\Corporation;

use Seat\Eveapi\Jobs\EsiBase;
use Seat\Eveapi\Models\Corporation\CorporationStructure;
use Seat\Eveapi\Models\Corporation\CorporationStructureService;
use Seat\Eveapi\Models\RefreshToken;
use Seat\Eveapi\Models\Universe\UniverseStructure;

/**
 * Class Structures.
 * @package Seat\Eveapi\Jobs\Corporation
 */
class Structures extends EsiBase
{
    /**
     * @var string
     */
    protected $method = 'get';

    /**
     * @var string
     */
    protected $endpoint = '/corporations/{corporation_id}/structures/';

    /**
     * @var string
     */
    protected $version = 'v2';

    /**
     * @var string
     */
    protected $scope = 'esi-corporations.read_structures.v1';

    /**
     * @var array
     */
    protected $roles = ['Station_Manager'];

    /**
     * @var array
     */
    protected $tags = ['corporation', 'structures'];

    /**
     * @var int
     */
    protected $page = 1;

    /**
     * @var \Illuminate\Support\Collection
     */
    protected $known_structures;

    /**
     * Structures constructor.
     *
     * @param \Seat\Eveapi\Models\RefreshToken|null $token
     */
    public function __construct(RefreshToken $token = null)
    {

        $this->known_structures = collect();

        parent::__construct($token);
    }

    /**
     * Execute the job.
     *
     * @throws \Throwable
     */
    public function handle()
    {

        if (! $this->preflighted()) return;

        while (true) {

            $structures = $this->retrieve([
                'corporation_id' => $this->getCorporationId(),
            ]);

            if ($structures->isCachedLoad()) return;

            collect($structures)->each(function ($structure) {

                // Ensure that we have an entry for this structure_id in the
                // UniverseStructures model. We set the name to unknown for now
                // but this will update when that models updater runs.
                $model = UniverseStructure::firstOrNew([
                    'structure_id' => $structure->structure_id,
                ])->fill([
                    'solar_system_id' => $structure->system_id,
                    'type_id'         => $structure->type_id,
                    'name'            => 'Unknown Structure',
                    'x'               => 0.0,
                    'y'               => 0.0,
                    'z'               => 0.0,
                ]);

                // Persist the structure only if it doesn't already exists
                if (! $model->exists) $model->save();

                CorporationStructure::firstOrNew([
                    'corporation_id' => $structure->corporation_id,
                    'structure_id'   => $structure->structure_id,
                ])->fill([
                    'type_id'                => $structure->type_id,
                    'system_id'              => $structure->system_id,
                    'profile_id'             => $structure->profile_id,
                    'fuel_expires'           => property_exists($structure, 'fuel_expires') ?
                        carbon($structure->fuel_expires) : null,
                    'state_timer_start'      => property_exists($structure, 'state_timer_start') ?
                        carbon($structure->state_timer_start) : null,
                    'state_timer_end'        => property_exists($structure, 'state_timer_end') ?
                        carbon($structure->state_timer_end) : null,
                    'unanchors_at'           => property_exists($structure, 'unanchors_at') ?
                        carbon($structure->unanchors_at) : null,
                    'state'                  => $structure->state,
                    'reinforce_weekday'      => $structure->reinforce_weekday,
                    'reinforce_hour'         => $structure->reinforce_hour,
                    'next_reinforce_weekday' => $structure->next_reinforce_weekday ?? null,
                    'next_reinforce_hour'    => $structure->next_reinforce_hour ?? null,
                    'next_reinforce_apply'   => property_exists($structure, 'next_reinforce_apply') ?
                        carbon($structure->next_reinforce_apply) : null,
                ])->save();

                if (property_exists($structure, 'services')) {

                    collect($structure->services)->each(function ($service) use ($structure) {

                        CorporationStructureService::firstOrNew([
                            'corporation_id' => $structure->corporation_id,
                            'structure_id'   => $structure->structure_id,
                            'name'           => $service->name,
                        ])->fill([
                            'state' => $service->state,
                        ])->save();
                    });

                    // Cleanup Services that may no longer be applicable to this structure.
                    CorporationStructureService::where('corporation_id', $structure->corporation_id)
                        ->where('structure_id', $structure->structure_id)
                        ->whereNotIn('name', collect($structure->services)
                            ->pluck('name')->flatten()->all())
                        ->delete();

                } else {

                    // If no services are defined on this structure, remove all of the
                    // ones we might have in the database.
                    CorporationStructureService::where('corporation_id', $structure->corporation_id)
                        ->where('structure_id', $structure->structure_id)
                        ->delete();
                }

                $this->known_structures->push($structure->structure_id);
            });

            if (! $this->nextPage($structures->pages))
                break;
        }

        // Cleanup services and structures that were not in the response.
        CorporationStructureService::where('corporation_id', $this->getCorporationId())
            ->whereNotIn('structure_id', $this->known_structures->flatten()->all())
            ->delete();

        CorporationStructure::where('corporation_id', $this->getCorporationId())
            ->whereNotIn('structure_id', $this->known_structures->flatten()->all())
            ->delete();
    }
}
