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

namespace Seat\Eveapi\Jobs\Alliances;

use Seat\Eveapi\Jobs\EsiBase;
use Seat\Eveapi\Models\Alliances\Alliance;

/**
 * Class Alliances.
 * @package Seat\Eveapi\Jobs\Alliances
 */
class Alliances extends EsiBase
{
    /**
     * @var string
     */
    protected $method = 'get';

    /**
     * @var string
     */
    protected $endpoint = '/alliances/';

    /**
     * @var string
     */
    protected $version = 'v1';

    /**
     * @var array
     */
    protected $tags = ['public', 'alliances'];

    /**
     * @throws \Exception
     * @throws \Throwable
     */
    public function handle()
    {

        $alliances = $this->retrieve();

        if ($alliances->isCachedLoad()) return;

        collect($alliances)->chunk(1000)->each(function ($chunk) {

            $records = $chunk->map(function ($alliance_id) {

                return [
                    'alliance_id' => $alliance_id,
                    'created_at'  => carbon(),
                    'updated_at'  => carbon(),
                ];
            });

            Alliance::insertOnDuplicateKey($records->toArray(), [
                'alliance_id',
                'updated_at',
            ]);
        });

        // Remove alliances that are closed / no longer listen in the API.
        Alliance::whereNotIn('alliance_id', collect($alliances)->flatten()->all())
            ->delete();

        // For each retrieved alliance ID, queue a dedicated job which will retrieve alliance information
        Alliance::all()->each(function ($alliance) {
            $job = new Info();
            $job->setAlliance($alliance);
            dispatch($alliance);
        });
    }
}
