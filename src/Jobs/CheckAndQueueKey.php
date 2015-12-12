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
use Pheal\Exceptions\APIException;
use Seat\Eveapi\Api\Account\APIKeyInfo;
use Seat\Eveapi\Exception\InvalidKeyTypeException;
use Seat\Eveapi\Helpers\JobContainer;
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
     *
     * @param \Seat\Eveapi\Helpers\JobContainer $fresh_job
     *
     * @throws \Seat\Eveapi\Exception\InvalidKeyTypeException
     */
    public function handle(JobContainer $fresh_job)
    {

        // Find the tracking record for this job
        $job_tracker = $this->trackOrDismiss();

        // If no tracking record was returned, we
        // will simply end here.
        if (!$job_tracker)
            return;

        if ($this->isEveApiDown($job_tracker))
            return;

        // Do the update work and catch any errors
        // that may come of it.
        try {

            $job_tracker->status = 'Working';
            $job_tracker->output = 'Started APIKeyInfo Update';
            $job_tracker->save();

            // https://api.eveonline.com/account/APIKeyInfo.xml.aspx
            (new APIKeyInfo())->setApi($this->job_payload->eve_api_key)->call();

            // Populate the new Job with some defaults
            $fresh_job->scope = 'Eve';
            $fresh_job->owner_id = $this->job_payload->eve_api_key->key_id;
            $fresh_job->eve_api_key = $this->job_payload->eve_api_key;

            // Now, based on the type of key, set the 'api'
            // value and queue an Authenticated Update job
            switch ($this->job_payload->eve_api_key->fresh()->info->type) {

                // Account & Character Key types are essentially
                // the same, except for the fact that one only
                // has one character and the other has all. All
                // updaters are multi character aware, so we will
                // treat both types exactly the same.
                case 'Account':
                case 'Character':
                    $fresh_job->api = 'Character';
                    break;

                case 'Corporation':
                    $fresh_job->api = 'Corporation';
                    break;

                default:
                    throw new InvalidKeyTypeException(
                        'Key type \'' . $this->job_payload->eve_api_key->type .
                        '\' is unknown. No update job was queued!');

                    return;
            }

            // Queue the actual update job with a populated
            // JobContainer
            $this->addUniqueJob(
                'Seat\Eveapi\Jobs\UpdateAuthenticated', $fresh_job);

            $job_tracker->status = 'Done';
            $job_tracker->output = null;
            $job_tracker->save();

        } catch (APIException $e) {

            $this->handleApiException(
                $job_tracker, $this->job_payload->eve_api_key, $e);

            return;

        } catch (\Exception $e) {

            $this->reportJobError($job_tracker, $e);

        }
    }
}
