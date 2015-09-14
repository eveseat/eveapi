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

namespace Seat\Eveapi\Api\Account;

use Seat\Eveapi\Api\Base;
use Seat\Eveapi\Models\AccountApiKeyInfo;
use Seat\Eveapi\Models\AccountApiKeyInfoCharacters;
use Seat\Eveapi\Models\EveApiKey;

/**
 * Class APIKeyInfo
 * @package Seat\Eveapi\Api\Account
 */
class APIKeyInfo extends Base
{

    /**
     * Run the Update
     *
     * @param \Seat\Eveapi\Models\EveApiKey $api_info
     */
    public function call(EveApiKey $api_info)
    {

        $result = $this->setKey(
            $api_info->key_id, $api_info->v_code)
            ->getPheal()
            ->accountScope
            ->APIKeyInfo();

        // Get or create the record...
        $key_info = AccountApiKeyInfo::firstOrNew([
            'keyID' => $api_info->key_id]);

        // ... and set its fields
        $key_info->fill([
            'accessMask' => $result->key->accessMask,
            'type'       => $result->key->type,
            'expires'    => strlen($result->key->expires) > 0 ? $result->key->expires : null
        ]);

        $key_info->save();

        // Next, lets process the characters for this API
        // Key. We need to be aware of the fact that it
        // is possible for characters to move around.

        // We create a list of known characters in the
        // database and remove from it as we update.
        // Once we are done, we will delete any of
        // the remaining characterID's
        $known_characters = AccountApiKeyInfoCharacters::where('keyID', $api_info->key_id)
            ->lists('characterID')->toArray();

        // Next up, we iterate of the chatacters we got from
        // the API response and update them keeping in mind
        // that we should remove them form the new array
        // we just built too
        foreach ($result->key->characters as $character) {

            $character_info = AccountApiKeyInfoCharacters::firstOrNew([
                'keyID'       => $api_info->key_id,
                'characterID' => $character->characterID]);

            $character_info->fill([
                'characterName'   => $character->characterName,
                'corporationID'   => $character->corporationID,
                'corporationName' => $character->corporationName
            ]);

            $character_info->save();

            // Remove this characterID from the known_characters
            $known_characters = array_diff(
                $known_characters, [$character->characterID]);
        }

        // Finally, remove the characters that are no longer
        // on this API Key. As a reminder, these are the
        // ids that remained after the previous update.
        AccountApiKeyInfoCharacters::whereIn('characterID', $known_characters)
            ->where('keyID', $api_info->key_id)
            ->delete();

        return;
    }

}
