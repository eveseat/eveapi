<?php
/*
This file is part of SeAT

Copyright (C) 2015  Leon Jacobs

This program is free software; you can redistribute it and/or modify
it under the terms of the GNU General Public License as published by
the Free Software Foundation; either version 2 of the License, or
(at your option) any later version.

This program is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
GNU General Public License for more details.

You should have received a copy of the GNU General Public License along
with this program; if not, write to the Free Software Foundation, Inc.,
51 Franklin Street, Fifth Floor, Boston, MA 02110-1301 USA.
*/

namespace Seat\Eveapi\Api\Eve;

use Seat\Eveapi\Api\Base;
use Seat\Eveapi\Models\Eve\CharacterInfo as CharacterInfoModel;
use Seat\Eveapi\Models\Eve\CharacterInfoEmploymentHistory;

/**
 * Class CharacterInfo
 * @package Seat\Eveapi\Api\Eve
 */
class CharacterInfo extends Base
{

    /**
     * Run the Update
     *
     * @param str $pub_character_id
     *
     * @return mixed|void
     */
    public function call($pub_character_id = null)
    {

        // This worker requires a slightly different bit
        // of logic to start off. This is because the api
        // endpoint works if you supply an api/vcode or
        // not.
        $pheal = $this->setScope('eve')->getPheal();

        // In the case of this being an update with a
        // specified characterID, use that one.
        if (!is_null($pub_character_id)) {

            $result = $pheal->CharacterInfo([
                'characterID' => $pub_character_id]);

            $this->_update_character_info($result);

        } else {

            // Otherwise, update all of the character on the
            // ApiKey that we got as normal
            foreach ($this->api_info->characters as $character) {

                $result = $pheal->CharacterInfo([
                    'characterID' => $character->characterID]);

                $this->_update_character_info($result);

            } // Foreach Character
        }

        return;
    }

    /**
     * @param $result
     */
    public function _update_character_info($result)
    {

        $character_info = CharacterInfoModel::firstOrNew([
            'characterID' => $result->characterID]);

        $character_info->fill([
            'characterName'     => $result->characterName,
            'race'              => $result->race,
            'bloodline'         => $result->bloodline,
            'bloodlineID'       => $result->bloodlineID,
            'ancestry'          => $result->ancestry,
            'ancestryID'        => $result->ancestryID,
            'corporationID'     => $result->corporationID,
            'corporation'       => $result->corporation,
            'corporationDate'   => $result->corporationDate,
            'securityStatus'    => $result->securityStatus,

            // Nullable values
            'accountBalance'    => $result->accountBalance,
            'skillPoints'       => $result->skillPoints,
            'nextTrainingEnds'  => $result->nextTrainingEnds,
            'shipName'          => $result->shipName,
            'shipTypeID'        => $result->shipTypeID,
            'shipTypeName'      => $result->shipTypeName,
            'allianceID'        => $result->allianceID,
            'alliance'          => $result->alliance,
            'allianceDate'      => $result->allianceDate,
            'lastKnownLocation' => $result->lastKnownLocation
        ]);

        $character_info->save();

        foreach ($result->employmentHistory as $employment) {

            CharacterInfoEmploymentHistory::firstOrCreate([
                'characterID'     => $result->characterID,
                'recordID'        => $employment->recordID,
                'corporationID'   => $employment->corporationID,
                'corporationName' => $employment->corporationName,
                'startDate'       => $employment->startDate
            ]);
        }

        return;
    }
}
