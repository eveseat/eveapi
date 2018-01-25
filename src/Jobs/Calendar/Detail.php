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
use Seat\Eveapi\Models\Calendar\CharacterCalendarEvent;
use Seat\Eveapi\Models\Calendar\CharacterCalendarEventDetail;

/**
 * Class Detail
 * @package Seat\Eveapi\Jobs\Calendar
 */
class Detail extends EsiBase
{
    /**
     * @var string
     */
    protected $method = 'get';

    /**
     * @var string
     */
    protected $endpoint = '/characters/{character_id}/calendar/{event_id}/';

    /**
     * @var string
     */
    protected $version = 'v3';

    /**
     * @var string
     */
    protected $scope = 'esi-calendar.read_calendar_events.v1';

    /**
     * @var array
     */
    protected $tags = ['character', 'calendar', 'detail'];

    /**
     * Execute the job.
     *
     * @return void
     * @throws \Exception
     */
    public function handle()
    {

        $event_ids = CharacterCalendarEvent::where('character_id', $this->getCharacterId())
            ->whereNotIn('event_id', function ($query) {

                $query->select('event_id')
                    ->from('character_calendar_event_details');

            })->pluck('event_id');

        $event_ids->each(function ($event_id) {

            $detail = $this->retrieve([
                'character_id' => $this->getCharacterId(),
                'event_id'     => $event_id,
            ]);

            CharacterCalendarEventDetail::firstOrCreate([
                'event_id'   => $event_id,
                'owner_id'   => $detail->owner_id,
                'owner_name' => $detail->owner_name,
                'duration'   => $detail->duration,
                'text'       => $detail->text,
                'owner_type' => $detail->owner_type,
            ]);
        });
    }
}
