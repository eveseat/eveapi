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

namespace Seat\Eveapi\Jobs\Sovereignty;

use Seat\Eveapi\Jobs\EsiBase;
use Seat\Eveapi\Models\Sovereignty\SovereigntyMap;

/**
 * Class Map.
 *
 * @package Seat\Eveapi\Jobs\Sovereignty
 */
class Map extends EsiBase
{
    /**
     * @var string
     */
    protected $method = 'get';

    /**
     * @var string
     */
    protected $endpoint = '/sovereignty/map/';

    /**
     * @var string
     */
    protected string $compatibility_date = '2025-07-20';

    /**
     * @var array
     */
    protected $tags = ['sovereignty'];

    /**
     * Execute the job.
     *
     * @throws \Throwable
     */
    public function handle()
    {
        $response = $this->retrieve();

        $systems = $response->getBody();

        collect($systems)->chunk(1000)->each(function ($chunk) {

            $records = $chunk->map(function ($system, $key) {
                return [
                    'system_id' => $system->system_id,
                    'alliance_id' => $system->alliance_id ?? null,
                    'corporation_id' => $system->corporation_id ?? null,
                    'faction_id' => $system->faction_id ?? null,
                    'created_at' => carbon(),
                    'updated_at' => carbon(),
                ];
            });

            SovereigntyMap::upsert($records->toArray(), [
                'system_id',
            ]);

        });

        SovereigntyMap::whereNotIn('system_id', collect($systems)
            ->pluck('system_id')->flatten()->all())
            ->delete();
    }
}
