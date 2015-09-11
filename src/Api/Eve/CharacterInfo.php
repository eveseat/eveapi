<?php
/*
The MIT License (MIT)

Copyright (c) 2015 Leon Jacobs
Copyright (c) 2015 eveseat

Permission is hereby granted, free of charge, to any person obtaining a copy
of this software and associated documentation files (the "Software"), to deal
in the Software without restriction, including without limitation the rights
to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
copies of the Software, and to permit persons to whom the Software is
furnished to do so, subject to the following conditions:

The above copyright notice and this permission notice shall be included in all
copies or substantial portions of the Software.

THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN THE
SOFTWARE.
*/

namespace Seat\Eveapi\Api\Eve;

use Seat\Eveapi\Api\Base;
use Seat\Eveapi\Models\EveApiKey;
use Seat\Eveapi\Models\EveCharacterInfo;
use Seat\Eveapi\Models\EveCharacterInfoEmploymentHistory;

/**
 * Class CharacterInfo
 * @package Seat\Eveapi\Api\Eve
 */
class CharacterInfo extends Base
{

    /**
     * Run the Update
     *
     * @param \Seat\Eveapi\Models\EveApiKey $api_info
     * @param str                           $pub_character_id
     */
    public function call(EveApiKey $api_info, $pub_character_id = null)
    {

        // This workers requires a slightly different bit
        // of logic to start off. This is because the api
        // endpoint works if you supply an api/vcode or
        // not.
        $pheal_handle = $this->setKey(
            $api_info->key_id, $api_info->v_code)
            ->getPheal();

        // In the case of this being an update with a
        // specified characterID, use that one.
        if (!is_null($pub_character_id)) {

            $result = $pheal_handle
                ->eveScope
                ->CharacterInfo([
                    'characterID' => $pub_character_id]);

            $this->_update_character_info($result);

        } else {

            // Otherwise, update all of the character on the
            // ApiKey that we got as normal
            foreach ($api_info->characters as $character) {

                $result = $pheal_handle
                    ->eveScope
                    ->CharacterInfo([
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

        $character_info = EveCharacterInfo::firstOrNew([
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

        // Process the Employment History
        foreach ($result->employmentHistory as $employment) {

            EveCharacterInfoEmploymentHistory::firstOrCreate([
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
