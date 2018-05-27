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
use Seat\Eveapi\Models\Corporation\CorporationMemberTitle;

/**
 * Class MembersTitles.
 * @package Seat\Eveapi\Jobs\Corporation
 */
class MembersTitles extends EsiBase
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
     * @var string
     */
    protected $scope = 'esi-corporations.read_titles.v1';

    /**
     * @var array
     */
    protected $roles = ['Director'];

    /**
     * @var array
     */
    protected $tags = ['corporation', 'member_titles'];

    /**
     * Execute the job.
     *
     * @throws \Throwable
     */
    public function handle()
    {

        if (! $this->preflighted()) return;

        $member_titles = $this->retrieve([
            'corporation_id' => $this->getCorporationId(),
        ]);

        if ($member_titles->isCachedLoad()) return;

        collect($member_titles)->filter(function ($member) {

            // Filter out members that do not have any titles.
            return count($member->titles) > 0;

        })->each(function ($member) {

            // Attach each title to the member
            collect($member->titles)->each(function ($title) use ($member) {

                CorporationMemberTitle::firstOrCreate([
                    'corporation_id' => $this->getCorporationId(),
                    'character_id'   => $member->character_id,
                    'title_id'       => $title,
                ]);
            });
        });

        // Cleanup members of this corporation that may no longer have any titles.
        CorporationMemberTitle::where('corporation_id', $this->getCorporationId())
            ->whereNotIn('character_id', collect($member_titles)->filter(function ($member) {

                return count($member->titles) > 0;

            })->pluck('character_id')->flatten()->all())
            ->delete();
    }
}
