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

namespace Seat\Eveapi\Api\Account;

use Seat\Eveapi\Api\Base;
use Seat\Eveapi\Models\AccountApiKeyInfo;
use Seat\Eveapi\Models\AccountApiKeyInfoCharacters;
use Seat\Eveapi\Models\EveApiKey;

class APIKeyInfo extends Base
{

    /**
     * Run the Ref Types Update
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