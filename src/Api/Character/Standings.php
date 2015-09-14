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

namespace Seat\Eveapi\Api\Character;

use Seat\Eveapi\Api\Base;
use Seat\Eveapi\Models\CharacterStanding;
use Seat\Eveapi\Models\EveApiKey;

/**
 * Class Standings
 * @package Seat\Eveapi\Api\Character
 */
class Standings extends Base
{

    /**
     * Run the Update
     *
     * @param \Seat\Eveapi\Models\EveApiKey $api_info
     */
    public function call(EveApiKey $api_info)
    {

        // Ofc, we need to process the update of all
        // of the characters on this key.
        foreach ($api_info->characters as $character) {

            $result = $this->setKey(
                $api_info->key_id, $api_info->v_code)
                ->getPheal()
                ->charScope
                ->Standings([
                    'characterID' => $character->characterID]);

            // We will receive 3 standings types from the API.
            // All of them are recorded in the same table, and
            // are distinguished by the type enum column.

            // Agents Standings
            foreach ($result->characterNPCStandings->agents as $standing)
                $this->_update_standing(
                    $character->characterID, $standing, 'agents');

            // NPCCorporations Standings
            foreach ($result->characterNPCStandings->NPCCorporations as $standing)
                $this->_update_standing(
                    $character->characterID, $standing, 'NPCCorporations');

            // Factionss Standings
            foreach ($result->characterNPCStandings->factions as $standing)
                $this->_update_standing(
                    $character->characterID, $standing, 'factions');
        }

        return;
    }

    /**
     * Update the character standing based on the type
     *
     * @param $characterID
     * @param $standing
     * @param $type
     */
    public function _update_standing($characterID, $standing, $type)
    {

        // Get or create the record...
        $standing_info = CharacterStanding::firstOrNew([
            'characterID' => $characterID,
            'fromID'      => $standing->fromID]);

        // ... and set its fields
        $standing_info->fill([
            'type'     => $type,
            'fromName' => $standing->fromName,
            'standing' => $standing->standing
        ]);

        $standing_info->save();

        return;
    }
}
