<?php

/*
 * This file is part of SeAT
 *
 * Copyright (C) 2015, 2016, 2017, 2018  Leon Jacobs
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

namespace Seat\Eveapi\Jobs\Calendar;


use Seat\Eveapi\Jobs\EsiBase;
use Seat\Eveapi\Models\Calendar\CharacterCalendarAttendee;
use Seat\Eveapi\Models\Calendar\CharacterCalendarEventDetail;

class Attendees extends EsiBase {

    protected $method = 'get';

    protected $endpoint = '/characters/{character_id}/calendar/{event_id}/attendees/';

    protected $version = 'v1';

    public function handle()
    {

        CharacterCalendarEventDetail::where('owner_id', $this->getCharacterId())->get()->each(function($event){

            $attendees = $this->retrieve([
                'character_id' => $this->getCharacterId(),
                'event_id'     => $event->event_id,
            ]);

            collect($attendees)->each(function($attendee) use ($event) {

                CharacterCalendarAttendee::firstOrNew([
                    'character_id'   => $this->getCharacterId(),
                    'event_id'       => $event->event_id,
                ])->fill([
                    'event_response' => $attendee->event_response,
                ])->save();

            });

            CharacterCalendarAttendee::where('event_id', $event->event_id)
                ->whereNotIn('character_id', collect($attendees)->pluck('character_id')->flatten()->all())
                ->delete();

        });

    }

}