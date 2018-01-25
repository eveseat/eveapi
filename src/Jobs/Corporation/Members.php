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
use Seat\Eveapi\Models\Corporation\CorporationMember;

/**
 * Class Members
 * @package Seat\Eveapi\Jobs\Corporation
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
    protected $endpoint = '/corporations/{corporation_id}/members/';

    /**
     * @var string
     */
    protected $version = 'v3';

    /**
     * @var string
     */
    protected $scope = 'esi-corporations.read_corporation_membership.v1';

    /**
     * @var array
     */
    protected $tags = ['corporation', 'members'];

    /**
     * Execute the job.
     *
     * @return void
     * @throws \Throwable
     */
    public function handle()
    {

        $members = $this->retrieve([
            'corporation_id' => $this->getCorporationId(),
        ]);

        collect($members)->each(function ($member_id) {

            CorporationMember::firstOrNew([
                'corporation_id' => $this->getCorporationId(),
                'character_id'   => $member_id,
            ])->save();

        });

        // Remove expelled members
        CorporationMember::where('corporation_id', $this->getCorporationId())
            ->whereNotIn('character_id', collect($members))
            ->delete();
    }
}
