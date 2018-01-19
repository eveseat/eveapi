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
use Seat\Eveapi\Models\Corporation\CorporationTitleMember;

/**
 * Class TitleMembers
 * @package Seat\Eveapi\Jobs\Corporation
 */
class TitleMembers extends EsiBase
{
    /**
     * @var string
     */
    protected $method = 'get';

    /**
     * @var string
     */
    protected $endpoint = '/corporations/{corporation_id}/members/titles/';

    /**
     * @var string
     */
    protected $version = 'v1';

    /**
     * @throws \Exception
     */
    public function handle()
    {

        $members = $this->retrieve([
            'corporation_id' => $this->getCorporationId(),
        ]);

        collect($members)->each(function ($member) {

            collect($member->titles)->each(function ($title) use ($member) {

                CorporationTitleMember::firstOrCreate([
                    'corporation_id' => $this->getCorporationId(),
                    'character_id'   => $member->character_id,
                    'title_id'       => $title,
                ]);
            });

            CorporationTitleMember::where('corporation_id', $this->getCorporationId())
                ->where('character_id', $member->character_id)
                ->whereNotIn('title_id', collect($member->titles)->flatten()->all())
                ->delete();
        });
    }
}
