<?php

/*
 * This file is part of SeAT
 *
 * Copyright (C) 2015 to 2022 Leon Jacobs
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

namespace Seat\Eveapi\Jobs\Character;

use Seat\Eveapi\Jobs\AbstractAuthCharacterJob;
use Seat\Eveapi\Jobs\Corporation\Info as CorporationInfoJob;
use Seat\Eveapi\Models\Character\CharacterInfo;
use Seat\Eveapi\Models\Character\CharacterLoyaltyPoints;
use Seat\Eveapi\Models\Corporation\CorporationInfo;

/**
 * Class Title.
 *
 * @package Seat\Eveapi\Jobs\Character
 */
class LoyaltyPoints extends AbstractAuthCharacterJob
{
    /**
     * @var string
     */
    protected $method = 'get';

    /**
     * @var string
     */
    protected $endpoint = '/characters/{character_id}/loyalty/points/';

    /**
     * @var int
     */
    protected $version = 'v1';

    /**
     * @var string
     */
    protected $scope = 'esi-characters.read_loyalty.v1';

    /**
     * @var array
     */
    protected $tags = ['character', 'loyalty points'];

    /**
     * Execute the job.
     *
     * @return void
     *
     * @throws \Exception
     * @throws \Throwable
     */
    public function handle()
    {
        //get character for whom to get the lp data
        $character_id = $this->getCharacterId();
        $character = CharacterInfo::find($character_id);

        //if the character doesn't exist, stop here
        if (is_null($character))
            return;

        //load lp data
        $loyalty_points = $this->retrieve([
            'character_id' => $character_id,
        ]);

        //don't run the job if the data is only cached
        if ($loyalty_points->isCachedLoad() && $character->loyalty_points()->count() > 0) return;

        //get the lp data as collection
        $loyalty_points = collect($loyalty_points);

        //go over each corporation where the character has lp
        $loyalty_points->each(function ($corporation_loyalty_points) use ($character_id) {
            //load npy corp data if we don't know the corporation. Per default, seat never loads data about npy corporations
            if(! CorporationInfo::where('corporation_id', $corporation_loyalty_points->corporation_id)->exists()){
                CorporationInfoJob::dispatch($corporation_loyalty_points->corporation_id);
            }

            //store the data
            CharacterLoyaltyPoints::updateOrCreate(
                [
                    'character_id'=>$character_id,
                    'corporation_id'=>$corporation_loyalty_points->corporation_id,
                ],
                [
                    'loyalty_points'=>$corporation_loyalty_points->loyalty_points,
                ]
            );
        });

        //I can't get the lp of a corp to exactly 0 and the documentation doesn't state what happens when you reach 0. I can imagine 2 cases: we get data with amount=0, or it disappears from the list.
        //remove lp data from corporations that don't appear in the esi data anymore
        CharacterLoyaltyPoints::where('character_id', $character_id)->whereNotIn('corporation_id', $loyalty_points->pluck('corporation_id'))->delete();
    }
}
