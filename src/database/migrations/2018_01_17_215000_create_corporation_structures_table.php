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
use Illuminate\Support\Facades\Schema;

class CreateCorporationStructuresTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {

        Schema::create('corporation_structures', function (Blueprint $table) {

            $table->bigInteger('corporation_id');
            $table->bigInteger('structure_id');
            $table->integer('type_id');
            $table->integer('system_id');
            $table->integer('profile_id');
            $table->dateTime('fuel_expires')->nullable();
            $table->dateTime('state_timer_start')->nullable();
            $table->dateTime('state_timer_end')->nullable();
            $table->dateTime('unanchors_at')->nullable();
            $table->enum('state', [
                'anchor_vulnerable', 'anchoring', 'armor_reinforce', 'armor_vulnerable',
                'fitting_invulnerable', 'hull_reinforce', 'hull_vulnerable', 'online_deprecated',
                'onlining_vulnerable', 'shield_vulnerable', 'unanchored', 'unknown',
            ]);
            $table->integer('reinforce_weekday');
            $table->integer('reinforce_hour');
            $table->integer('next_reinforce_weekday')->nullable();
            $table->integer('next_reinforce_hour')->nullable();
            $table->dateTime('next_reinforce_apply')->nullable();

            $table->primary(['corporation_id', 'structure_id'], 'corporation_structures_primary_key');
            $table->index('corporation_id');
            $table->index('structure_id');
            $table->index('system_id');
            $table->index('profile_id');
            $table->index('type_id');

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

        Schema::dropIfExists('corporation_structures');
    }
}
