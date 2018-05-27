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
use Seat\Eveapi\Models\Corporation\CorporationMedal;

/**
 * Class Medals.
 * @package Seat\Eveapi\Jobs\Corporation
 */
class Medals extends EsiBase
{
    /**
     * @var string
     */
    protected $method = 'get';

    /**
     * @var string
     */
    protected $endpoint = '/corporations/{corporation_id}/medals/';

    /**
     * @var string
     */
    protected $version = 'v1';

    /**
     * TODO: Add a local override property as this scope
     * does not need an ingame role.
     *
     * @var string
     */
    protected $scope = 'esi-corporations.read_medals.v1';

    /**
     * @var array
     */
    protected $tags = ['corporation', 'medals'];

    /**
     * @var int
     */
    protected $page = 1;

    /**
     * Execute the job.
     *
     * @return void
     * @throws \Throwable
     */
    public function handle()
    {

        if (! $this->preflighted()) return;

        while (true) {

            $medals = $this->retrieve([
                'corporation_id' => $this->getCorporationId(),
            ]);

            if ($medals->isCachedLoad()) return;

            collect($medals)->each(function ($medal) {

                CorporationMedal::firstOrNew([
                    'corporation_id' => $this->getCorporationId(),
                    'medal_id'       => $medal->medal_id,
                ])->fill([
                    'title'       => $medal->title,
                    'description' => $medal->description,
                    'creator_id'  => $medal->creator_id,
                    'created_at'  => carbon($medal->created_at),
                ])->save();

            });

            if (! $this->nextPage($medals->pages))
                break;
        }
    }
}
