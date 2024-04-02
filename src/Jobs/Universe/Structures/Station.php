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

use Illuminate\Bus\Batchable;
use Seat\Eveapi\Jobs\EsiBase;
use Seat\Eveapi\Mapping\Structures\UniverseStationMapping;
use Seat\Eveapi\Models\Universe\UniverseStation;
use Seat\Eveapi\Models\Universe\UniverseStationService;

/**
 * Class Stations.
 *
 * @package Seat\Eveapi\Jobs\Universe
 */
class Station extends EsiBase
{
    use Batchable;

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
    protected $tags = ['public', 'universe', 'structure'];

    /**
     * @var int
     */
    private int $station_id;

    /**
     * Stations constructor.
     *
     * @param  int  $station_id
     */
    public function __construct(int $station_id)
    {
        parent::__construct();

        $this->station_id = $station_id;
    }

    /**
     * Execute the job.
     *
     * @throws \Throwable
     */
    public function handle()
    {

        $response = $this->retrieve(['station_id' => $this->station_id]);
        $structure = $response->getBody();

        $model = UniverseStation::firstOrNew([
            'station_id' => $structure->station_id,
        ]);

        UniverseStationMapping::make($model, $structure, [
            'station_id' => function () use ($structure) {
                return $structure->station_id;
            },
        ])->save();

        collect($structure->services)->each(function ($service) use ($structure) {

            UniverseStationService::firstOrNew([
                'station_id' => $structure->station_id,
                'service_name' => $service,
            ])->save();

        });

        UniverseStationService::where('station_id', $structure->station_id)
            ->whereNotIn('service_name', collect($structure->services)
                ->pluck('name')->flatten()->all())
            ->delete();

    }
}
