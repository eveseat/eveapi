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

use Seat\Eveapi\Models\JobTracking;

/**
 * Class JobTracker
 * @package Seat\Eveapi\Traits
 */
trait JobTracker
{

    /**
     * Checks the Job Tracking table if the current job
     * has a tracking entry. If not, the job is just
     * deleted
     *
     * @return mixed
     */
    public function trackOrDismiss()
    {

        // Match the current job_id with the tracking
        // record we added when queuing the job
        $job_tracker = JobTracking::where('job_id',
            $this->job->getJobId())
            ->first();

        // If no tracking record is found, just put
        // the job back in the queue after a few
        // seconds. It could be that the job
        // to add it has not finished yet.
        if (!$job_tracker) {

            // Check that we have not come by this logic
            // for like the 10th time now.
            if ($this->attempts() < 10) {

                // Add the job back into the queue and wait
                // for 2 seconds before releasing it.
                $this->release(2);

                return null;
            }

            // Remove yourself from the queue
            // TODO: Log this event.
            $this->delete();

            return null;
        }

        // Return the Job Tracking handle
        return $job_tracker;
    }

    /**
     * Write diagnostic information to the Job Tracker
     *
     * @param \Seat\Eveapi\Models\JobTracking $job_tracker
     * @param \Exception                      $e
     */
    public function reportJobError(JobTracking $job_tracker, \Exception $e)
    {

        $job_tracker->status = 'Error';
        $job_tracker->output = 'Last status: ' . $job_tracker->output . PHP_EOL .
            'Error: ' . $e->getCode() . ': ' . $e->getMessage() . PHP_EOL .
            'File: ' . $e->getFile() . ':' . $e->getLine() . PHP_EOL .
            'Trace: ' . $e->getTraceAsString() . PHP_EOL .
            'Previous: ' . $e->getPrevious();
        $job_tracker->save();

        return;
    }
}