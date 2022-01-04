<?php

/*
 * This file is part of SeAT
 *
 * Copyright (C) 2015 to 2022 Leon Jacobs
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
 * Class RemoveCorporationIndustryMiningObserverDataSurrogateKey.
 */
class RemoveCorporationIndustryMiningObserverDataSurrogateKey extends Migration
{
    public function up()
    {
        Schema::table('corporation_industry_mining_observer_data', function (Blueprint $table) {
            $table->dropPrimary(['corporation_id', 'observer_id', 'recorded_corporation_id', 'character_id', 'type_id', 'last_updated']);
        });

        Schema::table('corporation_industry_mining_observer_data', function (Blueprint $table) {
            $table->bigIncrements('id')->first();
        });

        DB::statement('DELETE a FROM corporation_industry_mining_observer_data a
                       INNER JOIN corporation_industry_mining_observer_data b
                       WHERE a.id < b.id
                       AND a.observer_id = b.observer_id
                       AND a.recorded_corporation_id = b.recorded_corporation_id
                       AND a.character_id = b.character_id
                       AND a.type_id = b.type_id
                       AND a.last_updated = b.last_updated');

        Schema::table('corporation_industry_mining_observer_data', function (Blueprint $table) {
            $table->dropColumn('id');
        });

        Schema::table('corporation_industry_mining_observer_data', function (Blueprint $table) {
            $table->unique(['observer_id', 'recorded_corporation_id', 'character_id', 'type_id', 'last_updated'], 'corporation_industry_mining_observer_data_unique');
        });
    }

    public function down()
    {
        Schema::table('corporation_industry_mining_observer_data', function (Blueprint $table) {
            $table->dropUnique('corporation_industry_mining_observer_data_unique');
            $table->primary(['corporation_id', 'observer_id', 'recorded_corporation_id', 'character_id', 'type_id', 'last_updated'], 'corporation_industry_mining_observer_data_primary');
        });
    }
}
