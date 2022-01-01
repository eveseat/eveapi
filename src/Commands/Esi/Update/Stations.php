<?php

/*
 * This file is part of SeAT
 *
 * Copyright (C) 2015 to 2021 Leon Jacobs
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

namespace Seat\Eveapi\Commands\Esi\Update;

use Illuminate\Console\Command;
use Seat\Eveapi\Jobs\Universe\Stations as StationsJob;
use Seat\Eveapi\Models\Assets\CharacterAsset;
use Seat\Eveapi\Models\Assets\CorporationAsset;
use Seat\Eveapi\Models\Contracts\ContractDetail;
use Seat\Eveapi\Models\Corporation\CorporationInfo;
use Seat\Eveapi\Models\Universe\UniverseStation;

/**
 * Class Stations.
 *
 * @package Seat\Eveapi\Commands\Esi\Update
 */
class Stations extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'esi:update:stations';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Schedule updater jobs for stations information';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $stations = collect();

        // NPC stations using HQ
        CorporationInfo::whereNotIn('home_station_id', UniverseStation::FAKE_STATION_ID)
            ->select('home_station_id')
            ->orderBy('home_station_id')
            ->distinct()
            ->chunk(100, function ($corporations) use (&$stations) {
                $stations = $stations->merge($corporations->pluck('home_station_id')->toArray());
            });

        // NPC stations using character assets
        CharacterAsset::where('location_type', 'station')
            ->select('location_id')
            ->orderBy('location_id')
            ->distinct()
            ->chunk(100, function ($assets) use (&$stations) {
                $stations = $stations->merge($assets->pluck('location_id')->toArray());
            });

        // NPC stations using corporation assets
        CorporationAsset::where('location_type', 'station')
            ->select('location_id')
            ->orderBy('location_id')
            ->distinct()
            ->chunk(100, function ($assets) use (&$stations) {
                $stations = $stations->merge($assets->pluck('location_id')->toArray());
            });

        // NPC stations using from contract start locations
        ContractDetail::where('start_location_type', UniverseStation::class)
            ->select('start_location_id')
            ->orderBy('start_location_id')
            ->distinct()
            ->chunk(100, function ($locations) use (&$stations) {
                $stations = $stations->merge($locations->pluck('start_location_id')->toArray());
            });

        // NPC stations using from contract start locations
        ContractDetail::where('end_location_type', UniverseStation::class)
            ->select('end_location_id')
            ->orderBy('end_location_id')
            ->distinct()
            ->chunk(100, function ($locations) use (&$stations) {
                $stations = $stations->merge($locations->pluck('end_location_id')->toArray());
            });

        $stations = $stations->unique();

        $stations->chunk(100)->each(function ($chunk) {
            StationsJob::dispatch($chunk->toArray());
        });

        return $this::SUCCESS;
    }
}
