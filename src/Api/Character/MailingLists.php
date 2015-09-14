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
use Seat\Eveapi\Models\CharacterAccountBalance;
use Seat\Eveapi\Models\CharacterMailingList;
use Seat\Eveapi\Models\CharacterMailingListInfo;
use Seat\Eveapi\Models\CharacterMailMessage;
use Seat\Eveapi\Models\EveApiKey;

/**
 * Class MailingLists
 * @package Seat\Eveapi\Api\Character
 */
class MailingLists extends Base
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
                ->MailingLists([
                    'characterID' => $character->characterID]);

            // Characters can join/leave mailing lists at
            // any time. For this reason, we need to clean
            // the linking table and repopulate the list
            // memberships for this character.
            CharacterMailingList::where(
                'characterID', $character->characterID)->delete();

            // Re-populate the mailing lists and info if needed
            foreach ($result->mailingLists as $list) {

                CharacterMailingList::create([
                    'characterID' => $character->characterID,
                    'listID'      => $list->listID
                ]);

                // Add the list information if we are not already
                // aware of it.
                CharacterMailingListInfo::firstOrCreate([
                    'listID'      => $list->listID,
                    'displayName' => $list->displayName
                ]);
            }
        }

        return;
    }
}
