<?php
/*
This file is part of SeAT

Copyright (C) 2015  Leon Jacobs

This program is free software; you can redistribute it and/or modify
it under the terms of the GNU General Public License as published by
the Free Software Foundation; either version 2 of the License, or
(at your option) any later version.

This program is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
GNU General Public License for more details.

You should have received a copy of the GNU General Public License along
with this program; if not, write to the Free Software Foundation, Inc.,
51 Franklin Street, Fifth Floor, Boston, MA 02110-1301 USA.
*/

namespace Seat\Eveapi\Jobs;

use App\Jobs\Job;
use Illuminate\Foundation\Bus\DispatchesJobs;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Bus\SelfHandling;
use Illuminate\Contracts\Queue\ShouldQueue;

use Seat\Eveapi\Api\Eve\AllianceList;
use Seat\Eveapi\Api\Eve\ConquerableStationList;
use Seat\Eveapi\Api\Eve\ErrorList;
use Seat\Eveapi\Api\Eve\RefTypes;

use Seat\Eveapi\Traits\JobTracker;

/**
 * Class UpdateEve
 * @package Seat\Eveapi\Jobs
 */
class UpdateEve extends Job implements SelfHandling, ShouldQueue
{

    use InteractsWithQueue, SerializesModels, JobTracker;

    /**
     * Execute the job.
     *
     */
    public function handle()
    {

        // Find the tracking record for this job
        $job_tracker = $this->trackOrDismiss();

        // If no tracking record was returned, we
        // will simply end here.
        if (!$job_tracker)
            return;

        // Do the update work and catch any errors
        // that may come of it.
        try {

            $job_tracker->status = 'Working';
            $job_tracker->save();

            $job_tracker->output = 'Started RefTypes Update';
            $job_tracker->save();

            // https://api.eveonline.com/eve/RefTypes.xml.aspx
            $work = new RefTypes();
            $work->call();

            $job_tracker->output = 'Started ErrorList Update';
            $job_tracker->save();

            // https://api.eveonline.com/eve/ErrorList.xml.aspx
            $work = new ErrorList();
            $work->call();

            $job_tracker->output = 'Started ConquerableStationList Update';
            $job_tracker->save();

            // https://api.eveonline.com/eve/ConquerableStationList.xml.aspx
            $work = new ConquerableStationList();
            $work->call();

            $job_tracker->output = 'Started AllianceList Update';
            $job_tracker->save();

            // https://api.eveonline.com/eve/AllianceList.xml.aspx
            $work = new AllianceList();
            $work->call();

            $job_tracker->status = 'Done';
            $job_tracker->output = null;
            $job_tracker->save();

        } catch (\Exception $e) {

            $this->reportJobError($job_tracker, $e);

        }
    }
}
