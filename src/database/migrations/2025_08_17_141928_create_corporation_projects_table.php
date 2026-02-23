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

class CreateCorporationProjectsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('corporation_projects', function (Blueprint $table) {
            
            // The below are all obtainable from the list api
            
            $table->uuid('id')->primary();
            $table->bigInteger('corporation_id')->index(); // Index because the datatable will filter on it.
            $table->dateTime('last_modified');
            $table->string('name', length:61);
            $table->bigInteger('progress_current');
            $table->bigInteger('progress_desired');
            $table->double('reward_initial')->nullable();
            $table->double('reward_remaining')->nullable();
            $table->enum('state', ['Unspecified', 'Active', 'Closed', 'Completed', 'Expired', 'Deleted']);

            // The below only come from the details endpoint (which can also update some of the above)
            $table->enum('career', ['Unspecified', 'Explorer', 'Industrialist', 'Enforcer', 'Soldier of Fortune'])->nullable();
            $table->dateTime('created')->nullable();
            $table->string('description', length:1001)->nullable();
            $table->dateTime('expires')->nullable();
            $table->dateTime('finished')->nullable();
            $table->bigInteger('creator_id')->nullable();

            $table->bigInteger('contribution_participation_limit')->nullable();
            $table->double('contribution_reward')->nullable();
            $table->bigInteger('contribution_submission_limit')->nullable();
            $table->double('contribution_submission_multiplier')->nullable();

            $table->json('configuration')->nullable();

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
        Schema::drop('corporation_projects');
    }
}
