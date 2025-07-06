<?php

/*
 * This file is part of SeAT
 *
 * Copyright (C) 2015 to present Leon Jacobs
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
    public function created(CharacterAffiliation $affiliation){
        $this->handle($affiliation);
    }

    /**
     * @param  \Seat\Eveapi\Models\Character\CharacterAffiliation  $affiliation
     */
    public function updated(CharacterAffiliation $affiliation){
        $this->handle($affiliation);
    }

    /**
     * @param  \Seat\Eveapi\Models\Character\CharacterAffiliation  $affiliation
     */
    public function handle(CharacterAffiliation $affiliation)
    {
        if (! CorporationInfo::find($affiliation->corporation_id) && RefreshToken::withTrashed()->find($affiliation->character_id))
            dispatch(new CorporationInfoJob($affiliation->corporation_id))->onQueue('high');

        if (! empty($affiliation->alliance_id) && ! Alliance::find($affiliation->alliance_id))
            dispatch(new AllianceInfoJob($affiliation->alliance_id))->onQueue('high');
    }
}
