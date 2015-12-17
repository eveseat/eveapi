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

namespace Seat\Eveapi\Traits;

use Illuminate\Foundation\Bus\DispatchesJobs;
use Seat\Eveapi\Helpers\JobContainer;
use Seat\Eveapi\Models\JobTracking;

/**
 * Class JobManager
 * @package Seat\Eveapi\Traits
 */
trait JobManager
{

    use DispatchesJobs;

    /**
     * Adds a Job to the queue only if one does not
     * already exist.
     *
     * @param $job
     * @param $args
     *
     * @return mixed
     */
    public function addUniqueJob($job, JobContainer $args)
    {

        // Look for an existing job
        $job_id = JobTracking::where('owner_id', $args->owner_id)
            ->where('api', $args->api)
            ->whereIn('status', ['Queued', 'Working'])
            ->value('job_id');

        // Just return if the job already exists
        if ($job_id)
            return $job_id;

        // Add a new job onto the queue...
        $new_job = (new $job($args))->onQueue($args->queue);
        $job_id = $this->dispatch($new_job);

        // Check that the id we got back is a random
        // string and not 0. In fact, normal job_ids
        // are like a 32char string, so just check that
        // its more than 2. If its not, we can assume
        // the job itself was not sucesfully added.
        // If it actually is queued, it will get discarded
        // when trackOrDismiss() is called.
        if(strlen($job_id) < 2)
            return;

        // ...and add tracking information
        JobTracking::create([
            'job_id'   => $job_id,
            'owner_id' => $args->owner_id,
            'api'      => $args->api,
            'scope'    => $args->scope,
            'status'   => 'Queued'
        ]);

        return $job_id;

    }
}
