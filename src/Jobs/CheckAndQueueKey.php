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

namespace Seat\Eveapi\Jobs;

use Pheal\Exceptions\ConnectionException;
use Seat\Eveapi\Api\Account\APIKeyInfo;
use Seat\Eveapi\Exception\InvalidKeyTypeException;
use Seat\Eveapi\Helpers\JobPayloadContainer;
use Seat\Eveapi\Traits\JobManager;

/**
 * Class CheckAndQueueKey.
 * @package Seat\Eveapi\Jobs
 */
class CheckAndQueueKey extends Base
{
    use JobManager;

    /**
     * Execute the job.
     *
     * @return mixed|void
     * @throws \Seat\Eveapi\Exception\InvalidKeyTypeException
     */
    public function handle()
    {

        // Find the tracking record for this job. If there
        // is none, simply return and do nothing.
        if (! $this->trackOrDismiss())
            return;

        // Do the update work and catch any errors
        // that may come of it.
        try {

            // Update the Jobs status
            $this->updateJobStatus([
                'status' => 'Working',
                'output' => 'Started APIKeyInfo Update',
            ]);

            $this->writeInfoJobLog('Starting APIKeyInfo Update');

            // https://api.eveonline.com/account/APIKeyInfo.xml.aspx
            (new APIKeyInfo())->setApi($this->job_payload->eve_api_key)->call();
            $this->decrementErrorCounters();

            // Populate the new Job with some defaults
            $fresh_job = new JobPayloadContainer();
            $fresh_job->scope = 'Eve';
            $fresh_job->owner_id = $this->job_payload->eve_api_key->key_id;
            $fresh_job->eve_api_key = $this->job_payload->eve_api_key;
            $fresh_job->queue = $this->job_payload->queue;

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
                    $this->writeInfoJobLog('Key type is Account/Character');
                    $fresh_job->api = 'Character';
                    break;

                case 'Corporation':
                    $this->writeInfoJobLog('Key type is Corporation');
                    $fresh_job->api = 'Corporation';
                    break;

                default:
                    throw new InvalidKeyTypeException(
                        'Key type \'' . $this->job_payload->eve_api_key->type .
                        '\' is unknown. No update job was queued!');

                    return;
            }

            // Queue the actual update job with a populated
            // JobPayloadContainer
            $this->addUniqueJob(UpdateAuthenticated::class, $fresh_job);
            $this->markAsDone();

        } catch (ConnectionException $e) {

            $this->writeErrorJobLog('A ConnectionException occured. The error was: ' .
                $e->getMessage());
            $this->handleConnectionException($e);

            // TODO: Add some logging so that the keys
            // could be troubleshooted later
            $this->markAsDone();

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
            if ($e->getCode() == 403) {
                $this->writeErrorJobLog('A 403 ConnectionException occured. ' .
                    'The API key might be expired.');

                $this->disableKeyIfGracePeriodReached(
                    $this->job_payload->eve_api_key,
                    'Disabled due to possibly expired key. ' . $e->getCode() . ':' . $e->getMessage()
                );
            }
        }

    }
}
