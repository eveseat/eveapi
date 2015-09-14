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
