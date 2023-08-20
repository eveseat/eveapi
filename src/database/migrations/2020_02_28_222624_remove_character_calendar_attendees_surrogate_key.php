<?php

/*
 * This file is part of SeAT
 *
 * Copyright (C) 2015 to present Leon Jacobs
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

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Class RemoveCharacterCalendarAttendeesSurrogateKey.
 */
class RemoveCharacterCalendarAttendeesSurrogateKey extends Migration
{
    public function up()
    {
        Schema::table('character_calendar_attendees', function (Blueprint $table) {
            $table->dropPrimary(['event_id', 'character_id']);
            $table->unique(['event_id', 'character_id']);
        });

        Schema::table('character_calendar_attendees', function (Blueprint $table) {
            $table->bigIncrements('id')->first();
        });
    }

    public function down()
    {
        Schema::table('character_calendar_attendees', function (Blueprint $table) {
            $table->dropColumn('id');
            $table->dropUnique(['event_id', 'character_id']);
        });

        Schema::table('character_calendar_attendees', function (Blueprint $table) {
            $table->primary(['event_id', 'character_id']);
        });
    }
}
