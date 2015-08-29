<?php
/*
The MIT License (MIT)

Copyright (c) 2015 Leon Jacobs
Copyright (c) 2015 eveseat

Permission is hereby granted, free of charge, to any person obtaining a copy
of this software and associated documentation files (the "Software"), to deal
in the Software without restriction, including without limitation the rights
to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
copies of the Software, and to permit persons to whom the Software is
furnished to do so, subject to the following conditions:

The above copyright notice and this permission notice shall be included in all
copies or substantial portions of the Software.

THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN THE
SOFTWARE.
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