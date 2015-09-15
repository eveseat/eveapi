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
use Seat\Eveapi\Models\CharacterMailMessageBody;

/**
 * Class MailBodies
 * @package Seat\Eveapi\Api\Character
 */
class MailBodies extends Base
{

    /**
     * Run the Update
     *
     * @return mixed|void
     */
    public function call()
    {

        $pheal = $this->setScope('char')->getPheal();

        // Loop the key characters
        foreach ($this->api_info->characters as $character) {

            // Get a list of messageIDs that we do not have mail
            // bodies for. These ID's will be used to try and
            // pull the bodies using this api key
            $message_ids = DB::table('character_mail_messages')
                ->where('characterID', $character->characterID)
                ->whereNotIn('messageID', function ($query) {

                    $query->select('messageID')
                        ->from('character_mail_message_bodies');

                })
                ->lists('messageID');

            // It is possible to provide a comma seperated list
            // of messageIDs to the MailBodies endpoint. Pheal
            // caches XML's on disk by file name. To prevent file
            // names from becoming too long, we will chunk the
            // ids we want to update.
            foreach (array_chunk($message_ids, 10) as $message_id_chunk) {

                $result = $pheal->MailBodies([
                    'characterID' => $character->characterID,
                    'ids'         => implode(',', $message_id_chunk)]);

                // Populate the mail bodies
                foreach ($result->messages as $body) {

                    CharacterMailMessageBody::create([
                        'messageID' => $body->messageID,
                        'body'      => $body->__toString()
                    ]);
                }
            } // Foreach messageID chunk
        }

        return;
    }
}
