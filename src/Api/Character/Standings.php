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
