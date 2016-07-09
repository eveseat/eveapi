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

use App\Jobs\Job;
use Illuminate\Contracts\Bus\SelfHandling;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Pheal\Exceptions\APIException;
use Pheal\Exceptions\ConnectionException;
use Pheal\Exceptions\PhealException;
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
            $this->decrementErrorCounters();

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

            $this->markAsDone($job_tracker);

        } catch (APIException $e) {

            $this->handleApiException(
                $job_tracker, $this->job_payload->eve_api_key, $e);

            // TODO: Add some logging so that the keys
            // could be troubleshooted later
            $this->markAsDone($job_tracker);

            return;

        } catch (ConnectionException $e) {

            $this->handleConnectionException($e);

            // TODO: Add some logging so that the keys
            // could be troubleshooted later
            $this->markAsDone($job_tracker);

            // In the case of the Account/APIKeyInfo call, CCP
            // will respond with a HTTP 403, and then have error code
            // 222 in the reponse XML detailing the fact that the
            // API key is expired. JobTracker/handleApiException would
            // have handled this correctly, except for the fact that
            // the reponse ExceptionCode from the Pheal ConnectionException
            // is the HTTP 403 we got, and not the 222.
            // For this reason, we are going to assume there is something
            // wrong with the API key if we get an HTTP 403 on this
            // worker to check the key.
            if ($e->getCode() == 403)
                $this->job_payload->eve_api_key->update([
                    'enabled'    => false,
                    'last_error' => 'Disabled due to possibly expired key. ' .
                        $e->getCode() . ':' . $e->getMessage()
                ]);

        } catch (PhealException $e) {

            // Typically, this will be the XML parsing errors that
            // will end up here. Catch them and handle them as a connection
            // exception for now.
            $this->handleConnectionException($e);

            // TODO: Add some logging
            $this->markAsDone($job_tracker);

        } catch (\Exception $e) {

            $this->reportJobError($job_tracker, $e);

        }
    }

    /**
     * Update the job tracker to a failed state
     */
    public function failed()
    {

        $this->handleFailedJob($this->job_payload);

        return;

    }

    /**
     * Mark a Job as Done
     *
     * @param  $job_tracker
     */
    public function markAsDone($job_tracker)
    {

        $job_tracker->status = 'Done';
        $job_tracker->output = null;
        $job_tracker->save();

        return;
    }
}
