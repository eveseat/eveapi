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

namespace Seat\Eveapi\Commands\Esi\Update;

use Illuminate\Console\Command;
use Seat\Eveapi\Bus\Character;
use Seat\Eveapi\Bus\Corporation;
use Seat\Eveapi\Jobs\Alliances\Alliances;
use Seat\Eveapi\Jobs\Fittings\Insurances;
use Seat\Eveapi\Jobs\Market\Prices;
use Seat\Eveapi\Jobs\Sovereignty\Map;
use Seat\Eveapi\Jobs\Sovereignty\Structures;
use Seat\Eveapi\Jobs\Universe\Names;
use Seat\Eveapi\Jobs\Universe\Stations;
use Seat\Eveapi\Models\Assets\CorporationAsset;
use Seat\Eveapi\Models\Character\CharacterInfo;
use Seat\Eveapi\Models\Corporation\CorporationInfo;
use Seat\Eveapi\Models\Universe\UniverseStation;

/**
 * Class PublicInfo.
 * @package Seat\Eveapi\Commands\Esi\Update
 */
class PublicInfo extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'esi:update:public';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Schedule updater jobs for public information';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        // NPC stations using HQ
        CorporationInfo::whereNotIn('home_station_id', UniverseStation::FAKE_STATION_ID)
            ->select('home_station_id')
            ->orderBy('home_station_id')
            ->distinct()
            ->chunk(100, function ($corporations) {
                Stations::dispatch($corporations->pluck('home_station_id')->toArray());
            });

        // NPC stations using corporation assets
        CorporationAsset::where('location_type', 'station')
            ->select('location_id')
            ->orderBy('location_id')
            ->distinct()
            ->chunk(100, function ($assets) {
                Stations::dispatch($assets->pluck('location_id')->toArray());
            });

        Map::dispatch();
        Structures::dispatch();
        Names::dispatch();
        Alliances::dispatch();
        Prices::dispatch();
        Insurances::dispatch();

        CharacterInfo::doesntHave('refresh_token')->each(function ($character) {
            (new Character($character->character_id))->fire();
        });

        CorporationInfo::all()->each(function ($corporation) {
            (new Corporation($corporation->corporation_id))->fire();
        });
    }
}
