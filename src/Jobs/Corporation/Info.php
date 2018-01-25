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
use Seat\Eveapi\Models\Corporation\CorporationInfo;

/**
 * Class Info
 * @package Seat\Eveapi\Jobs\Corporation
 */
class Info extends EsiBase
{
    /**
     * @var string
     */
    protected $method = 'get';

    /**
     * @var string
     */
    protected $endpoint = '/corporations/{corporation_id}/';

    /**
     * @var string
     */
    protected $version = 'v4';

    /**
     * @var array
     */
    protected $tags = ['corporation', 'info'];

    /**
     * Execute the job.
     *
     * @return void
     * @throws \Throwable
     */
    public function handle()
    {

        $corporation = $this->retrieve([
            'corporation_id' => $this->getCorporationId(),
        ]);

        CorporationInfo::firstOrNew([
            'corporation_id' => $this->getCorporationId(),
        ])->fill([
            'name'            => $corporation->name,
            'ticker'          => $corporation->ticker,
            'member_count'    => $corporation->member_count,
            'ceo_id'          => $corporation->ceo_id,
            'alliance_id'     => $corporation->alliance_id ?? null,
            'description'     => $corporation->description ?? null,
            'tax_rate'        => $corporation->tax_rate,
            'date_founded'    => property_exists($corporation, 'date_founded') ?
                carbon($corporation->date_founded) : null,
            'creator_id'      => $corporation->creator_id,
            'url'             => $corporation->url ?? null,
            'faction_id'      => $corporation->faction_id ?? null,
            'home_station_id' => $corporation->home_station_id ?? null,
            'shares'          => $corporation->shares ?? null,
        ])->save();
    }
}
