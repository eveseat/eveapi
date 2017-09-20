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

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

class DeleteOutdatedPeopleGroup extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        // drop all orphan people and group relations
        DB::table('person_members')
            ->whereNotIn('person_id', function ($query) {
                $query->select('id')->from('people');
            })
            ->delete();

        DB::table('person_members')
            ->whereNotIn('key_id', function ($query) {
                $query->select('key_id')->from('eve_api_keys');
            })
            ->delete();

        DB::table('people')
            ->whereNotIn('id', function ($query) {
                $query->select('person_id')->from('person_members');
            })
            ->delete();

        DB::table('people')
            ->whereNotIn('main_character_id', function ($query) {
                $query->select('characterID')->from('account_api_key_info_characters');
            })
            ->delete();
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
    }
}
