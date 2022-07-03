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
use Seat\Eveapi\Models\Character\CharacterInfo;
use Seat\Eveapi\Models\Character\CharacterLoyaltyPoints;
use Seat\Eveapi\Models\Corporation\CorporationInfo;
use Seat\Eveapi\Jobs\Corporation\Info as CorporationInfoJob;

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
        $loyalty_points = $this->retrieve([
            'character_id' => $this->getCharacterId(),
        ]);

        $character_id = $this->getCharacterId();
        $character = CharacterInfo::find($character_id);

        if (is_null($character))
            return;

        if ($loyalty_points->isCachedLoad() && $character->loyalty_points()->count() > 0) return;

        $loyalty_points = collect($loyalty_points);

        $loyalty_points->each(function ($loyalty_point) use ($character_id) {
            //load npy corp data if required
            if(!CorporationInfo::where("corporation_id",$loyalty_point->corporation_id)->exists()){
                CorporationInfoJob::dispatch($loyalty_point->corporation_id);
            }

            CharacterLoyaltyPoints::updateOrCreate(
                [
                    "character_id"=>$character_id,
                    "corporation_id"=>$loyalty_point->corporation_id
                ],
                [
                    "loyalty_points"=>$loyalty_point->loyalty_points
                ]
            );
        });

        CharacterLoyaltyPoints::where("character_id",$character_id)->whereNotIn("corporation_id",$loyalty_points->pluck("corporation_id"))->delete();
    }
}
