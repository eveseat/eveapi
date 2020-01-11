<?php

/*
 * This file is part of SeAT
 *
 * Copyright (C) 2015, 2016, 2017, 2018, 2019  Leon Jacobs
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
use Seat\Eveapi\Models\Alliances\AllianceMember;

/**
 * Class Members.
 * @package Seat\Eveapi\Jobs\Alliances
 */
class Members extends EsiBase
{
    /**
     * @var int
     */
    protected $alliance_id;

    /**
     * @var string
     */
    protected $method = 'get';

    /**
     * @var string
     */
    protected $endpoint = '/alliances/{alliance_id}/corporations/';

    /**
     * @var string
     */
    protected $version = 'v1';

    /**
     * @var array
     */
    protected $tags = ['alliances', 'members'];

    /**
     * Members constructor.
     *
     * @param int $alliance_id
     */
    public function __construct(int $alliance_id)
    {
        $this->alliance_id = $alliance_id;

        array_push($this->tags, $alliance_id);
    }

    /**
     * @throws \Throwable
     */
    public function handle()
    {

        $corporations = $this->retrieve([
            'alliance_id' => $this->alliance_id,
        ]);

        if ($corporations->isCachedLoad()) return;

        $corporation_ids = collect($corporations);

        $corporation_ids->each(function ($corporation_id) {

            AllianceMember::firstOrCreate([
                'alliance_id' => $this->alliance_id,
                'corporation_id' => $corporation_id,
            ]);
        });

        AllianceMember::where('alliance_id', $this->alliance_id)
            ->whereNotIn('corporation_id', $corporation_ids->flatten()->all())
            ->delete();
    }
}
