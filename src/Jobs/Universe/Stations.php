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

use Seat\Eveapi\Jobs\EsiBase;
use Seat\Eveapi\Models\Sovereignty\SovereigntyStructure;
use Seat\Eveapi\Models\Universe\UniverseStation;
use Seat\Eveapi\Models\Universe\UniverseStationService;

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
     * Execute the job
     *
     * @throws \Throwable
     */
    public function handle()
    {

        // filtering public structures on outpost typeID
        // 12242, 12294, 12295 are conquerable outpost
        // 21642 is caldari outpost
        // 21644 is amarr outpost
        // 21645 is gallente outpost
        // 21646 is minmatar outpost

        $structures = SovereigntyStructure::whereIn('structure_type_id', [12242, 12294, 12295, 21642, 21644, 21645, 21646])
                                ->get();

        $structures->each(function($structure){

            $outpost = $this->retrieve(['station_id' => $structure->structure_id]);

            UniverseStation::firstOrNew([
                'station_id' => $outpost->station_id,
            ])->fill([
                'type_id'    => $outpost->type_id,
                'name'       => $outpost->name,
                'owner'      => $outpost->owner ?? null,
                'race_id'    => $outpost->race_id ?? null,
                'x'          => $outpost->position->x,
                'y'          => $outpost->position->y,
                'z'          => $outpost->position->z,
                'system_id'  => $outpost->system_id,
                'reprocessing_efficiency' => $outpost->reprocessing_efficiency,
                'reprocessing_stations_take' => $outpost->reprocessing_stations_take,
                'max_dockable_ship_volume' => $outpost->max_dockable_ship_volume,
                'office_rental_cost' => $outpost->office_rental_cost,
            ])->save();

            collect($outpost->services)->each(function($service) use ($outpost) {

                UniverseStationService::firstOrNew([
                    'station_id' => $outpost->station_id,
                    'service_name' => $service,
                ])->save();

            });

            UniverseStationService::where('station_id', $outpost->station_id)
                ->whereNotIn('service_name', collect($outpost->services)->pluck('name')->flatten()->all())
                ->delete();

        });

    }

}
