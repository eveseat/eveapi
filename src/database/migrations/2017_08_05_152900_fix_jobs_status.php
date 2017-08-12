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
use Seat\Eveapi\Models\Character\IndustryJob as CharacterIndustryJob;
use Seat\Eveapi\Models\Corporation\IndustryJob as CorporationIndustryJob;

class FixJobsStatus extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {

        // Heads up that this is a heavy migration.
        echo 'Running migration to fix industry jobs status. ' .
            'This may take some time to complete depending of database size.' . PHP_EOL;

        echo 'Starting with Characters Jobs...' . PHP_EOL;

        // update character jobs according to CCP bugs
        // https://eveonline-third-party-documentation.readthedocs.io/en/latest/xmlapi/character/char_industryjobs.html#known-bugs
        $jobs = CharacterIndustryJob::where('status', 1)
            ->whereDate('endDate', '<=', date('Y-m-d H:i:s'))
            ->get();

        foreach ($jobs as $job) {

            $job->status = 3;
            $job->save();
        }

        echo 'Character Jobs has been updated.' . PHP_EOL;

        echo 'Starting with Corporations Jobs...' . PHP_EOL;

        // update corporation jobs according to CCP bugs
        // https://eveonline-third-party-documentation.readthedocs.io/en/latest/xmlapi/corporation/corp_industryjobs.html#known-bugs
        $jobs = CorporationIndustryJob::where('status', 1)
            ->whereDate('endDate', '<=', date('Y-m-d H:i:s'))
            ->get();

        foreach ($jobs as $job) {

            $job->status = 3;
            $job->save();
        }

        echo 'Corporation Jobs has been updated.' . PHP_EOL;

        echo 'CCP Job status fix has been successfully applied.' . PHP_EOL;
    }
}
