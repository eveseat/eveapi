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

class CreateCorporationContainerLogsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {

        Schema::create('corporation_container_logs', function (Blueprint $table) {

            $table->bigInteger('corporation_id');
            $table->dateTime('logged_at');
            $table->bigInteger('container_id');
            $table->integer('container_type_id');
            $table->bigInteger('character_id');
            $table->bigInteger('location_id');
            $table->string('location_flag');
            $table->string('action');
            $table->enum('password_type', ['config', 'general'])->nullable();
            $table->integer('type_id')->nullable();
            $table->integer('quantity')->nullable();
            $table->integer('old_config_bitmask')->nullable();
            $table->integer('new_config_bitmask')->nullable();

            $table->primary(['corporation_id', 'container_id', 'logged_at'],
                'corporation_container_logs_primary_key');
            $table->index('corporation_id');
            $table->index('container_id');
            $table->index('character_id');
            $table->index('location_id');
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

        Schema::dropIfExists('corporation_container_logs');
    }
}
