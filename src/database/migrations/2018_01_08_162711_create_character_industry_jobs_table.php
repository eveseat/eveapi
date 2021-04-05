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

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateCharacterIndustryJobsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {

        Schema::create('character_industry_jobs', function (Blueprint $table) {

            $table->bigInteger('character_id');
            $table->bigInteger('job_id');

            $table->bigInteger('installer_id');
            $table->bigInteger('facility_id');
            $table->bigInteger('station_id');
            $table->integer('activity_id');
            $table->bigInteger('blueprint_id');
            $table->integer('blueprint_type_id');
            $table->bigInteger('blueprint_location_id');
            $table->bigInteger('output_location_id');
            $table->integer('runs');
            $table->double('cost')->nullable();
            $table->integer('licensed_runs')->nullable();
            $table->float('probability')->nullable();
            $table->integer('product_type_id')->nullable();
            $table->enum('status', ['active', 'cancelled', 'delivered', 'paused', 'ready', 'reverted']);
            $table->integer('duration');
            $table->dateTime('start_date');
            $table->dateTime('end_date');
            $table->dateTime('pause_date')->nullable();
            $table->dateTime('completed_date')->nullable();
            $table->integer('completed_character_id')->nullable();
            $table->integer('successful_runs')->nullable();

            $table->primary(['character_id', 'job_id']);
            $table->index('character_id');
            $table->index('installer_id');
            $table->index('station_id');
            $table->index('blueprint_id');
            $table->index('status');

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

        Schema::dropIfExists('character_industry_jobs');
    }
}
