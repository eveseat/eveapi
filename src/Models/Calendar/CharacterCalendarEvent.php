<?php

/*
 * This file is part of SeAT
 *
 * Copyright (C) 2015 to 2021 Leon Jacobs
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

namespace Seat\Eveapi\Models\Calendar;

use Illuminate\Database\Eloquent\Model;

/**
 * Class CharacterCalendarEvent.
 *
 * @package Seat\Eveapi\Models\Calendar
 */
class CharacterCalendarEvent extends Model
{
    /**
     * @var bool
     */
    protected static $unguarded = true;

    /**
     * @param $value
     */
    public function setEventDateAttribute($value)
    {
        $this->attributes['event_date'] = is_null($value) ? null : carbon($value);
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasOne
     */
    public function detail()
    {

        return $this->hasOne(CharacterCalendarEventDetail::class, 'event_id', 'event_id')
            ->withDefault();
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function attendees()
    {

        return $this->hasMany(CharacterCalendarAttendee::class, 'event_id', 'event_id');
    }
}
