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
use Seat\Eveapi\Models\Corporation\CorporationStanding;

/**
 * Class Standings.
 * @package Seat\Eveapi\Jobs\Corporation
 */
class Standings extends EsiBase
{
    /**
     * @var string
     */
    protected $method = 'get';

    /**
     * @var string
     */
    protected $endpoint = '/corporations/{corporation_id}/standings/';

    /**
     * @var int
     */
    protected $version = 'v1';

    /**
     * @var string
     */
    protected $scope = 'esi-corporations.read_standings.v1';

    /**
     * @var array
     */
    protected $tags = ['corporation', 'standings'];

    /**
     * @var int
     */
    protected $page = 1;

    /**
     * Execute the job.
     *
     * @throws \Throwable
     */
    public function handle()
    {

        if (! $this->preflighted()) return;

        while (true) {

            $standings = $this->retrieve([
                'corporation_id' => $this->getCorporationId(),
            ]);

            if ($standings->isCachedLoad()) return;

            collect($standings)->chunk(100)->each(function ($chunk) {

                $records = $chunk->map(function ($standing, $key) {
                    return [
                        'corporation_id' => $this->getCorporationId(),
                        'from_type'      => $standing->from_type,
                        'from_id'        => $standing->from_id,
                        'standing'       => $standing->standing,
                        'created_at'     => carbon(),
                        'updated_at'     => carbon(),
                    ];
                });

                CorporationStanding::insertOnDuplicateKey($records->toArray(), [
                    'corporation_id',
                    'from_type',
                    'from_id',
                    'standing',
                    'updated_at',
                ]);
            });

            if (! $this->nextPage($standings->pages))
                break;
        }
    }
}
