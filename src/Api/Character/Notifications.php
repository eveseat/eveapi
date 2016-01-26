<?php
/*
This file is part of SeAT

Copyright (C) 2015, 2016  Leon Jacobs

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
use Seat\Eveapi\Models\Character\Notifications as NotificationsModel;

/**
 * Class Notifications
 * @package Seat\Eveapi\Api\Character
 */
class Notifications extends Base
{

    /**
     * Run the Update
     *
     * @return mixed|void
     */
    public function call()
    {

        $pheal = $this->setScope('char')->getPheal();

        foreach ($this->api_info->characters as $character) {

            $result = $pheal->Notifications([
                'characterID' => $character->characterID]);

            foreach ($result->notifications as $notification) {

                NotificationsModel::firstOrCreate([
                    'characterID'    => $character->characterID,
                    'notificationID' => $notification->notificationID,
                    'typeID'         => $notification->typeID,
                    'senderID'       => $notification->senderID,
                    'senderName'     => $notification->senderName,
                    'sentDate'       => $notification->sentDate,
                    'read'           => $notification->read
                ]);
            }
        }

        return;
    }
}
