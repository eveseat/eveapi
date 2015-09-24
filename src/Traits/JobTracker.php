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

use Illuminate\Support\Facades\Log;
use Seat\Eveapi\Models\EveApiKey;
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
            Log::error(
                'Error finding a JobTracker for job ' . $this->job->getJobID());
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

        // Write an entry to the log file.
        Log::error(
            $job_tracker->api . '/' . $job_tracker->scope . ' for '
            . $job_tracker->owner_id . ' failed with ' . get_class($e)
            . ': ' . $e->getMessage() . '. See the job tracker for more ' .
            'information.');

        // Prepare some useful information about the error.
        $output = 'Last Updater: ' . $job_tracker->output . PHP_EOL;
        $output .= PHP_EOL;
        $output .= 'Exception: ' . get_class($e) . PHP_EOL;
        $output .= 'Error Code: ' . $e->getCode() . PHP_EOL;
        $output .= 'Error Message: ' . $e->getMessage() . PHP_EOL;
        $output .= 'File: ' . $e->getFile() . ' - Line: ' . $e->getLine() . PHP_EOL;
        $output .= PHP_EOL;
        $output .= 'Traceback: ' . $e->getTraceAsString() . PHP_EOL;

        $job_tracker->status = 'Error';
        $job_tracker->output = $output;
        $job_tracker->save();

        return;
    }

    /**
     * Load worker classes from the configuration
     * file based on the 'api' type in the
     * job tracker. This method honors the class
     * definitions in eveapi.config.disabled_workers
     * as well as the key specific disabled_workers.
     *
     * @param \Seat\Eveapi\Models\JobTracking $job
     *
     * @return mixed
     */
    public function load_workers(JobTracking $job)
    {

        $type = strtolower($job->api);
        $workers = config('eveapi.workers.' . $type);

        $global_disabled_workers = config(
            'eveapi.config.disabled_workers.' . $type);

        $key_disabled_workers = $job->owner_id == 0 ?
            [] : json_decode(EveApiKey::find($job->owner_id)->disabled_calls);

        // Check that we do not have a null result
        // for the key specific disabled workers
        if (is_null($key_disabled_workers))
            $key_disabled_workers = [];

        // Check if any workers are ignored either via
        // the global config or this specific key.
        foreach ($workers as $worker)
            if (in_array($worker, array_merge($global_disabled_workers, $key_disabled_workers)))
                // Remove the worker.
                $workers = array_diff($workers, [$worker]);

        return $workers;

    }

    /**
     * Attempt to take the appropriate action based on the
     * EVE API Exception.
     *
     * @param \Seat\Eveapi\Models\JobTracking $job_tracker
     * @param \Seat\Eveapi\Models\EveApiKey   $api_key
     * @param \Exception                      $e
     *
     * @throws \Exception
     */
    public function handleApiException(JobTracking $job_tracker, EveApiKey $api_key, $e)
    {

        // Errors from the EVE API should be treated seriously. If
        // these are ignored, one may risk having the calling IP
        // banned entirely. We don't want that, so lets check
        // and act accordingly based on the error code. We also rely
        // entirely on PhealNG to pass us the proper error codes.
        switch ($e->getCode()) {

            // "API key authentication failure."
            case 202:
                // "Authentication failure."
            case 203:
            case 204:
                // "Authentication failure."
            case 205:
                // "Authentication failure."
            case 210:
                // "Authentication failure (final pass)."
            case 212:
                // The API is probably entirely wrong.
                $api_key->update([
                    'enabled'    => false,
                    'last_error' => $e->getCode() . ':' . $e->getMessage()
                ]);

                break;

            // "Invalid Corporation Key. Key owner does not fullfill role
            // requirements anymore."
            case 220:
                // Owner of the corporation key doesnt have hes roles anymore?
                $api_key->update([
                    'enabled'    => false,
                    'last_error' => $e->getCode() . ':' . $e->getMessage()
                ]);

                break;

            // "Illegal page request! Please verify the access granted by the key you are using!."
            case 221:
                // Not 100% sure how to handle this one. This call has no
                // access mask requirement...
                $api_key->update([
                    'last_error' => $e->getCode() . ':' . $e->getMessage()
                ]);

                break;

            // "Key has expired. Contact key owner for access renewal."
            case 222:
                // We have a invalid key. Expired or deleted.
                $api_key->update([
                    'enabled'    => false,
                    'last_error' => $e->getCode() . ':' . $e->getMessage()
                ]);

                break;

            // "Authentication failure. Legacy API keys can no longer be
            // used. Please create a new key on support.eveonline.com
            // and make sure your application supports Customizable
            // API Keys."
            case 223:
                // The API we are working with is waaaaaay too old.
                $api_key->update([
                    'enabled'    => false,
                    'last_error' => $e->getCode() . ':' . $e->getMessage()
                ]);

                break;

            // "Web site database temporarily disabled."
            case 901:
                // The EVE API Database is apparently down, so mark the
                // server as 'down' in the cache so that subsequent
                // calls don't fail because of this.
                \Cache::put('eve_api_down', true, 30);

                break;

            // "EVE backend database temporarily disabled.""
            case 902:
                // The EVE API Database is apparently down, so mark the
                // server as 'down' in the cache so that subsequent
                // calls don't fail because of this.
                \Cache::put('eve_api_down', true, 30);

                break;

            // "Your IP address has been temporarily blocked because it
            // is causing too many errors. See the cacheUntil
            // timestamp for when it will be opened again.
            // IPs that continually cause a lot of errors
            // in the API will be permanently banned,
            // please take measures to minimize
            // problematic API calls from your
            // application."
            case 904:
                // If we are rate limited, set the status of the eveapi
                // server to 'down' in the cache so that subsequent
                // calls don't fail because of this.

                // Get time of IP ban in minutes, rounded up to the next whole minute
                $time = round((
                        $e->cached_until_unixtime - $e->request_time_unixtime) / 60, 0, PHP_ROUND_HALF_UP);
                \Cache::put('eve_api_down', true, $time);

                break;

            // We got a problem we don't know what to do with, so log
            // and throw the exception so that the can debug it.
            default:
                throw $e;
                break;

        }

        // Update the Job itself with the error information
        $this->reportJobError($job_tracker, $e);

        return;
    }

}
