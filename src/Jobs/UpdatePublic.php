<?php
/*
This file is part of SeAT

Copyright (C) 2015, 2016  Leon Jacobs

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

use Exception;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Pheal\Exceptions\APIException;
use Pheal\Exceptions\ConnectionException;
use Seat\Eveapi\Helpers\JobContainer;
use Seat\Eveapi\Traits\JobTracker;

/**
 * Class UpdatePublic
 * @package Seat\Eveapi\Jobs
 */
class UpdatePublic implements ShouldQueue
{

    use InteractsWithQueue, Queueable, SerializesModels, JobTracker;

    /**
     * The JobContainer Instance containing
     * extra payload information.
     *
     * @var
     */
    protected $job_payload;

    /**
     * Create a new job instance.
     *
     * @param \Seat\Eveapi\Helpers\JobContainer $job_payload
     */
    public function __construct(JobContainer $job_payload)
    {

        $this->job_payload = $job_payload;
    }

    /**
     * Execute the job.
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
        $job_tracker->status = 'Working';
        $job_tracker->save();

        // Attempt to run the Updaters based on the
        // type of key we are working with.
        try {

            foreach ($this->load_workers($job_tracker) as $worker) {

                // Check that the EveApi is considered up
                if ($this->isEveApiDown($job_tracker))
                    return;

                try {

                    $job_tracker->output = 'Processing: '
                        . class_basename($worker);
                    $job_tracker->save();

                    // Perform the update
                    (new $worker)->call();
                    $this->decrementErrorCounters();

                } catch (APIException $e) {

                    // If we should not continue, simply return.
                    if (!$this->handleApiException(
                        $job_tracker, $this->job_payload->eve_api_key, $e)
                    )
                        return;

                    continue;

                } catch (ConnectionException $e) {

                    $this->handleConnectionException($e);
                    continue;
                }

            } // Foreach worker

        } catch (\Exception $e) {

            $this->reportJobError($job_tracker, $e);

            return;
        }

        $job_tracker->status = 'Done';
        $job_tracker->output = null;
        $job_tracker->save();

    }

    /**
     * Update the job tracker to a failed state
     *
     * @param \Exception $exception
     */
    public function failed(Exception $exception)
    {

        $this->handleFailedJob($this->job_payload, $exception);

        return;

    }

}
