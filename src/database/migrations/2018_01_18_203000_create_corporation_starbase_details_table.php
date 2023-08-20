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

class CreateCorporationStarbaseDetailsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {

        Schema::create('corporation_starbase_details', function (Blueprint $table) {

            $table->bigInteger('corporation_id');
            $table->bigInteger('starbase_id');
            $table->enum('fuel_bay_view', [
                'alliance_member',
                'config_starbase_equipment_role',
                'corporation_member',
                'starbase_fuel_technician_role',
            ]);
            $table->enum('fuel_bay_take', [
                'alliance_member',
                'config_starbase_equipment_role',
                'corporation_member',
                'starbase_fuel_technician_role',
            ]);
            $table->enum('anchor', [
                'alliance_member',
                'config_starbase_equipment_role',
                'corporation_member',
                'starbase_fuel_technician_role',
            ]);
            $table->enum('unanchor', [
                'alliance_member',
                'config_starbase_equipment_role',
                'corporation_member',
                'starbase_fuel_technician_role',
            ]);
            $table->enum('online', [
                'alliance_member',
                'config_starbase_equipment_role',
                'corporation_member',
                'starbase_fuel_technician_role',
            ]);
            $table->enum('offline', [
                'alliance_member',
                'config_starbase_equipment_role',
                'corporation_member',
                'starbase_fuel_technician_role',
            ]);
            $table->boolean('allow_corporation_members');
            $table->boolean('allow_alliance_members');
            $table->boolean('use_alliance_standings');
            $table->decimal('attack_standing_threshold')->nullable();
            $table->decimal('attack_security_status_threshold')->nullable();
            $table->boolean('attack_if_other_security_status_dropping');
            $table->boolean('attack_if_at_war');

            $table->primary(['corporation_id', 'starbase_id'], 'corporation_starbase_details_primary_key');
            $table->index('corporation_id');
            $table->index('starbase_id');

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {

        Schema::dropIfExists('corporation_starbase_details');
    }
}
