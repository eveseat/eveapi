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
use Illuminate\Contracts\Bus\SelfHandling;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Pheal\Exceptions\AccessException;
use Pheal\Exceptions\APIException;
use Seat\Eveapi\Traits\JobTracker;

/**
 * Class UpdateAuthenticated
 * @package Seat\Eveapi\Jobs
 */
class UpdateAuthenticated extends Job implements SelfHandling, ShouldQueue
{

    use InteractsWithQueue, SerializesModels, JobTracker;

    /**
     * The EveApiKey instance
     *
     * @var
     */
    protected $eve_api_key;

    /**
     * Create a new job instance.
     *
     * @param $eve_api_key
     */
    public function __construct($eve_api_key)
    {

        $this->eve_api_key = $eve_api_key;
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

                try {

                    $job_tracker->output = 'Processing: '
                        . class_basename($worker);
                    $job_tracker->save();

                    // Perform the update
                    (new $worker)->setApi($this->eve_api_key)->call();

                } catch (AccessException $e) {

                    // TODO: Write to some audit log file maybe?
                }

            } // Foreach worker

        } catch (APIException $e) {

            $this->handleApiException($job_tracker, $this->eve_api_key, $e);

            return;

        } catch (\Exception $e) {

            $this->reportJobError($job_tracker, $e);

            return;
        }

        $job_tracker->status = 'Done';
        $job_tracker->output = null;
        $job_tracker->save();
    }

}
