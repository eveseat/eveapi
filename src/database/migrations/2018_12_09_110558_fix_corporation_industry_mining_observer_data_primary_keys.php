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

class FixCorporationIndustryMiningObserverDataPrimaryKeys extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('corporation_industry_mining_observer_data', function (Blueprint $table) {
            $engine = DB::getDriverName();

            switch ($engine) {
                case 'mysql':
                    $table->dropPrimary();
                    break;
                case 'pgsql':
                case 'postgresql':
                    // workaround for pgsql drop instruction : https://github.com/laravel/framework/issues/42804
                    $table->dropUnique('corporation_mining_observer_data_pkey');
                    break;
            }

            $table->primary(['corporation_id', 'observer_id', 'recorded_corporation_id', 'character_id', 'type_id', 'last_updated'],
                'obeserver_data_primary');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('corporation_industry_mining_observer_data', function (Blueprint $table) {
            $engine = DB::getDriverName();

            switch ($engine) {
                case 'mysql':
                    $table->dropPrimary();
                    break;
                case 'pgsql':
                case 'postgresql':
                    // workaround for pgsql drop instruction : https://github.com/laravel/framework/issues/42804
                    $table->dropUnique('obeserver_data_primary');
                    break;
            }

            $table->primary(['corporation_id', 'observer_id', 'recorded_corporation_id', 'character_id', 'type_id'],
                'obeserver_data_primary');
        });
    }
}
