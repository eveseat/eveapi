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
use Seat\Eveapi\Models\Alliances\AllianceMember;

/**
 * Class Members.
 * @package Seat\Eveapi\Jobs\Alliances
 */
class Members extends EsiBase
{
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
    protected $tags = ['public', 'alliances', 'members'];

    /**
     * @throws \Exception
     */
    public function handle()
    {

        if (! $this->preflighted()) return;

        Alliance::all()->each(function ($alliance) {

            $corporations = $this->retrieve([
                'alliance_id' => $alliance->alliance_id,
            ]);

            if ($corporations->isCachedLoad()) return;

            collect($corporations)->chunk(1000)->each(function ($chunk) use ($alliance) {

                $records = $chunk->map(function ($corporation_id) use ($alliance) {

                    return [
                        'alliance_id'    => $alliance->alliance_id,
                        'corporation_id' => $corporation_id,
                        'created_at'     => carbon(),
                        'updated_at'     => carbon(),
                    ];
                });

                AllianceMember::insertOnDuplicateKey($records->toArray(), [
                    'alliance_id',
                    'corporation_id',
                    'updated_at',
                ]);
            });

            AllianceMember::where('alliance_id', $alliance->alliance_id)
                ->whereNotIn('corporation_id', collect($corporations)->flatten()->all())
                ->delete();

        });
    }
}
