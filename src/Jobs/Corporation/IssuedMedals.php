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
use Seat\Eveapi\Models\Corporation\CorporationIssuedMedal;

/**
 * Class IssuedMedals.
 * @package Seat\Eveapi\Jobs\Corporation
 */
class IssuedMedals extends EsiBase
{
    /**
     * @var string
     */
    protected $method = 'get';

    /**
     * @var string
     */
    protected $endpoint = '/corporations/{corporation_id}/medals/issued/';

    /**
     * @var string
     */
    protected $version = 'v1';

    /**
     * @var string
     */
    protected $scope = 'esi-corporations.read_medals.v1';

    /**
     * @var array
     */
    protected $roles = ['Director'];

    /**
     * @var array
     */
    protected $tags = ['corporation', 'issued_medals'];

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

                CorporationIssuedMedal::firstOrNew([
                    'corporation_id' => $this->getCorporationId(),
                    'medal_id'       => $medal->medal_id,
                    'character_id'   => $medal->character_id,
                ])->fill([
                    'reason'    => $medal->reason,
                    'status'    => $medal->status,
                    'issuer_id' => $medal->issuer_id,
                    'issued_at' => carbon($medal->issued_at),
                ])->save();

            });

            if (! $this->nextPage($medals->pages))
                break;
        }
    }
}
