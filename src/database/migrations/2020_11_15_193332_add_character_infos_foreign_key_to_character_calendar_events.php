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
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Class AddCharacterInfosForeignKeyToCharacterCalendarEvents.
 */
class AddCharacterInfosForeignKeyToCharacterCalendarEvents extends Migration
{
    public function up()
    {
        Schema::table('character_calendar_events', function (Blueprint $table) {

            // preventatively drop any orphan character events
            DB::table('character_calendar_events')
                ->leftJoin('character_infos', 'character_calendar_events.character_id', '=', 'character_infos.character_id')
                ->whereNull('character_infos.character_id')
                ->delete();

            $table->foreign('character_id')
                ->references('character_id')
                ->on('character_infos')
                ->onUpdate('no action')
                ->onDelete('cascade');
        });
    }

    public function down()
    {
        Schema::table('character_calendar_events', function (Blueprint $table) {
            $table->dropForeign(['character_id']);
        });
    }
}
