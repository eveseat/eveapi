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
use Seat\Eveapi\Models\CharacterUpcomingCalendarEvent;

/**
 * Class UpcomingCalendarEvents
 * @package Seat\Eveapi\Api\Character
 */
class UpcomingCalendarEvents extends Base
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

            $result = $pheal->UpcomingCalendarEvents([
                'characterID' => $character->characterID]);

            foreach ($result->upcomingEvents as $event) {

                $event_info = CharacterUpcomingCalendarEvent::firstOrNew([
                    'characterID' => $character->characterID,
                    'eventID'     => $event->eventID]);

                $event_info->fill([
                    'ownerID'     => $event->ownerID,
                    'ownerName'   => $event->ownerName,
                    'eventDate'   => $event->eventDate,
                    'eventTitle'  => $event->eventTitle,
                    'duration'    => $event->duration,
                    'importance'  => $event->importance,
                    'response'    => $event->response,
                    'eventText'   => $event->eventText,
                    'ownerTypeID' => $event->ownerTypeID
                ]);

                $event_info->save();
            }
        }

        return;
    }
}
