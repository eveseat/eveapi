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

use Seat\Eveapi\Api\Account\APIKeyInfo;
use Seat\Eveapi\Helpers\JobContainer;
use Seat\Eveapi\Models\AccountApiKeyInfo;
use Seat\Eveapi\Traits\JobManager;
use Seat\Eveapi\Traits\JobTracker;

/**
 * Class CheckAndQueueKey
 * @package Seat\Eveapi\Jobs
 */
class CheckAndQueueKey extends Job implements SelfHandling, ShouldQueue
{

    use InteractsWithQueue, SerializesModels, JobTracker, JobManager;

    /**
     * The EveApiKey instance
     *
     * @var
     */
    protected $eve_api_key;

    /**
     * Create a new job instance.
     *
     */
    public function __construct($eve_api_key)
    {

        $this->eve_api_key = $eve_api_key;
    }

    /**
     * Execute the job.
     *
     * @param \Seat\Eveapi\Helpers\JobContainer $fresh_job
     */
    public function handle(JobContainer $fresh_job)
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
            $job_tracker->output = 'Started APIKeyInfo Update';
            $job_tracker->save();

            // https://api.eveonline.com/account/APIKeyInfo.xml.aspx
            (new APIKeyInfo())->setApi($this->eve_api_key)->call();

            // Now, based on the type of key, queue another job
            // that will run with the actual updates. We need
            // to pull a fresh instance as it just updated.
            switch (
                AccountApiKeyInfo::find($this->eve_api_key->key_id)
                ->value('type')
            ) {

                // Account & Character Key types are essentially
                // the same, except for the fact that one only
                // has one character and the other has all.
                case 'Account':
                case 'Character':
                    $fresh_job->scope = 'Eve';
                    $fresh_job->api = 'Character';
                    $fresh_job->owner_id = $this->eve_api_key->key_id;
                    $fresh_job->eve_api_key = $this->eve_api_key;

                    $job_id = $this->addUniqueJob(
                        'Seat\Eveapi\Jobs\UpdateCharacter', $fresh_job);

                    $job_tracker->output = 'Character Update Job ' . $job_id . ' queued';
                    $job_tracker->save();
                    break;

                case 'Corporation':
                    $fresh_job->scope = 'Eve';
                    $fresh_job->api = 'Corporation';
                    $fresh_job->owner_id = $this->eve_api_key->key_id;
                    $fresh_job->eve_api_key = $this->eve_api_key;

                    $job_id = $this->addUniqueJob(
                        'Seat\Eveapi\Jobs\UpdateCorporation', $fresh_job);

                    $job_tracker->output = 'Corporation Update Job ' . $job_id . ' queued';
                    $job_tracker->save();
                    break;

                default:
                    $job_tracker->status = 'Error';
                    $job_tracker->output = 'Key type \'' . $this->eve_api_key->type .
                        '\' is unknown. No update job was queued!';
                    $job_tracker->save();

                    return;
            }

            $job_tracker->status = 'Done';
            $job_tracker->output = null;
            $job_tracker->save();

        } catch (\Exception $e) {

            $this->reportJobError($job_tracker, $e);

        }
    }
}
