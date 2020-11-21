<?php

/*
 * This file is part of SeAT
 *
 * Copyright (C) 2015 to 2020 Leon Jacobs
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
 * Class AddCorporationStarbasesForeignKeyToCorporationStarbaseFuels.
 */
class AddCorporationStarbasesForeignKeyToCorporationStarbaseFuels extends Migration
{
    public function up()
    {
        Schema::table('corporation_starbase_fuels', function (Blueprint $table) {

            // preventatively drop any orphan corporation starbase fuel row
            DB::table('corporation_starbase_fuels')
                ->leftJoin('corporation_starbases', 'corporation_starbase_fuels.starbase_id', '=', 'corporation_starbases.starbase_id')
                ->whereNull('corporation_starbases.starbase_id')
                ->delete();

            $table->foreign('starbase_id')
                ->references('starbase_id')
                ->on('corporation_starbases')
                ->onUpdate('no action')
                ->onDelete('cascade');
        });
    }

    public function down()
    {
        Schema::table('corporation_starbase_fuels', function (Blueprint $table) {
            $table->dropForeign(['starbase_id']);
        });
    }
}
