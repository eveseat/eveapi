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
 * Class Info
 * @package Seat\Eveapi\Jobs\Alliances
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
    protected $endpoint = '/alliances/{alliance_id}/';

    /**
     * @var string
     */
    protected $version = 'v3';

    /**
     * @var array
     */
    protected $tags = ['public', 'alliances', 'info'];

    /**
     * Handle the job.
     */
    public function handle()
    {

        Alliance::all()->each(function ($alliance) {

            $info = $this->retrieve([
                'alliance_id' => $alliance->alliance_id,
            ]);

            Alliance::find($alliance->alliance_id)->fill([
                'name'                    => $info->name,
                'creator_id'              => $info->creator_id,
                'creator_corporation_id'  => $info->creator_corporation_id,
                'ticker'                  => $info->ticker,
                'executor_corporation_id' => $info->executor_corporation_id ?? null,
                'date_founded'            => carbon($info->date_founded),
                'faction_id'              => $info->faction_id ?? null,
            ])->save();
        });
    }
}
