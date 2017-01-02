<?php

/*
 * This file is part of SeAT
 *
 * Copyright (C) 2015, 2016, 2017  Leon Jacobs
 *
 * This program is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 2 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License along
 * with this program; if not, write to the Free Software Foundation, Inc.,
 * 51 Franklin Street, Fifth Floor, Boston, MA 02110-1301 USA.
 */

namespace Seat\Eveapi\Api\Character;

use Seat\Eveapi\Api\Base;
use Seat\Eveapi\Models\Character\MailMessage;

/**
 * Class MailMessagese.
 * @package Seat\Eveapi\Api\Character
 */
class MailMessages extends Base
{
    /**
     * Run the Update.
     *
     * @return mixed|void
     */
    public function call()
    {

        $pheal = $this->setScope('char')->getPheal();

        foreach ($this->api_info->characters as $character) {

            $this->writeJobLog('mailmessages',
                'Processing characterID: ' . $character->characterID);

            $result = $pheal->MailMessages([
                'characterID' => $character->characterID, ]);

            $this->writeJobLog('mailmessages',
                'API responded with ' . count($result->messages) . ' messages');

            foreach ($result->messages as $message) {

                MailMessage::firstOrCreate([
                    'characterID'        => $character->characterID,
                    'messageID'          => $message->messageID,
                    'senderID'           => $message->senderID,
                    'senderName'         => $message->senderName,
                    'sentDate'           => $message->sentDate,
                    'title'              => $message->title,
                    'toCorpOrAllianceID' => $message->toCorpOrAllianceID === '' ?
                        null : $message->toCorpOrAllianceID,
                    'toCharacterIDs'     => $message->toCharacterIDs === '' ?
                        null : $message->toCharacterIDs,
                    'toListID'           => $message->toListID === '' ?
                        null : $message->toListID,
                ]);
            }
        }

    }
}
