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

use Illuminate\Support\Facades\DB;
use Seat\Eveapi\Api\Base;
use Seat\Eveapi\Models\CharacterNotificationsText;
use Seat\Eveapi\Models\EveApiKey;

/**
 * Class NotificationTexts
 * @package Seat\Eveapi\Api\Character
 */
class NotificationTexts extends Base
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

            // Get a list of notificationIDs that we do
            // not have the text content for.
            $notification_ids = DB::table('character_notifications')
                ->where('characterID', $character->characterID)
                ->whereNotIn('notificationID', function ($query) {

                    $query->select('notificationID')
                        ->from('character_notifications_texts');

                })
                ->lists('notificationID');

            // It is possible to provide a comma seperated list
            // of notificationIDs to the NotificationTexts
            // endpoint. Pheal caches XML's on disk by file name.
            // To prevent file names from becoming too long, we
            // will chunk the ids we want to update.
            foreach (array_chunk($notification_ids, 10) as $notification_id_chunk) {

                $result = $this->setKey(
                    $api_info->key_id, $api_info->v_code)
                    ->getPheal()
                    ->charScope
                    ->NotificationTexts([
                        'characterID' => $character->characterID,
                        'ids'         => implode(',', $notification_id_chunk)]);

                // Populate the mail bodies
                foreach ($result->notifications as $notification) {

                    CharacterNotificationsText::create([
                        'notificationID' => $notification->notificationID,
                        'text'           => $notification->__toString()
                    ]);
                }
            } // Foreach messageID chunk
        }

        return;
    }
}
