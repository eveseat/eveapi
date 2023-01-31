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

namespace Seat\Eveapi\Observers;

use Seat\Eveapi\Jobs\Alliances\Info as AllianceInfoJob;
use Seat\Eveapi\Jobs\Corporation\Info as CorporationInfoJob;
use Seat\Eveapi\Models\Alliances\Alliance;
use Seat\Eveapi\Models\Character\CharacterAffiliation;
use Seat\Eveapi\Models\Corporation\CorporationInfo;
use Seat\Eveapi\Models\Corporation\CorporationRole;
use Seat\Eveapi\Models\RefreshToken;

/**
 * Class CharacterAffiliationObserver.
 *
 * @package Seat\Eveapi\Observers
 */
class CharacterAffiliationObserver
{
    /**
     * @param  \Seat\Eveapi\Models\Character\CharacterAffiliation  $affiliation
     */
    public function created(CharacterAffiliation $affiliation)
    {
        if (! CorporationInfo::find($affiliation->corporation_id) && RefreshToken::withTrashed()->find($affiliation->character_id))
            dispatch(new CorporationInfoJob($affiliation->corporation_id))->onQueue('high');

        if (! empty($affiliation->alliance_id) && ! Alliance::find($affiliation->alliance_id))
            dispatch(new AllianceInfoJob($affiliation->alliance_id))->onQueue('high');
    }

    /**
     * @param  \Seat\Eveapi\Models\Character\CharacterAffiliation  $affiliation
     */
    public function saving(CharacterAffiliation $affiliation) {
        //if a character changed corporation, remove his corporation roles
        //it must run in the saving observer so that we can still see the old affiliation
        //first, check if the character changed corporations
        $is_same_corp = CharacterAffiliation::where('character_id', $affiliation->character_id)
            ->where('corporation_id', $affiliation->corporation_id)
            ->exists();
        if(! $is_same_corp){
            //make sure to update observers, so squads get updated. This means we actually have to load the model!
            $roles = CorporationRole::where('character_id', $affiliation->character_id)->get();
            foreach ($roles as $role){
                $role->delete();
            }
        }
    }
}
