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

use Seat\Eseye\Containers\EsiResponse;
use Seat\Eveapi\Jobs\EsiBase;
use Seat\Eveapi\Models\Corporation\CorporationInfo;
use Seat\Eveapi\Models\Sovereignty\SovereigntyStructure;
use Seat\Eveapi\Models\Universe\UniverseStation;
use Seat\Eveapi\Models\Universe\UniverseStationService;

/**
 * Class Stations.
 * @package Seat\Eveapi\Jobs\Universe
 */
class Stations extends EsiBase
{
    /**
     * @var string
     */
    protected $method = 'get';

    /**
     * @var string
     */
    protected $endpoint = '/universe/stations/{station_id}/';

    /**
     * @var string
     */
    protected $version = 'v2';

    /**
     * @var array
     */
    protected $tags = ['public', 'universe', 'stations'];

    /**
     * Execute the job.
     *
     * @throws \Throwable
     */
    public function handle()
    {

        // filtering public structures on outpost typeID
        $structure_filter = [
            12242, 12294, 12295, // conquerable outpost
            21642,               // caldari outpost
            21644,               // amarr outpost
            21645,               // gallente outpost
            21646,               // minmatar outpost
        ];

        // NPC stations
        CorporationInfo::all()->each(function ($corporation) {

            $station = $this->retrieve(['station_id' => $corporation->home_station_id]);

            $this->updateStructure($station);

        });

        // conquerable outposts
        SovereigntyStructure::whereIn('structure_type_id', $structure_filter)->get()
            ->each(function ($structure) {

                $outpost = $this->retrieve(['station_id' => $structure->structure_id]);

                $this->updateStructure($outpost);

            });
    }

    /**
     * @param \Seat\Eseye\Containers\EsiResponse $structure
     */
    private function updateStructure(EsiResponse $structure)
    {

        UniverseStation::firstOrNew([
            'station_id' => $structure->station_id,
        ])->fill([
            'type_id'                    => $structure->type_id,
            'name'                       => $structure->name,
            'owner'                      => $structure->owner ?? null,
            'race_id'                    => $structure->race_id ?? null,
            'x'                          => $structure->position->x,
            'y'                          => $structure->position->y,
            'z'                          => $structure->position->z,
            'system_id'                  => $structure->system_id,
            'reprocessing_efficiency'    => $structure->reprocessing_efficiency,
            'reprocessing_stations_take' => $structure->reprocessing_stations_take,
            'max_dockable_ship_volume'   => $structure->max_dockable_ship_volume,
            'office_rental_cost'         => $structure->office_rental_cost,
        ])->save();

        collect($structure->services)->each(function ($service) use ($structure) {

            UniverseStationService::firstOrNew([
                'station_id'   => $structure->station_id,
                'service_name' => $service,
            ])->save();

        });

        UniverseStationService::where('station_id', $structure->station_id)
            ->whereNotIn('service_name', collect($structure->services)
                ->pluck('name')->flatten()->all())
            ->delete();
    }
}
