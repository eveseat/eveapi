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

use Cache;
use Carbon\Carbon;
use Exception;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use Seat\Eveapi\Helpers\JobPayloadContainer;
use Seat\Eveapi\Models\Eve\ApiKey;
use Seat\Eveapi\Models\JobTracking;
use Seat\Services\Helpers\AnalyticsContainer;
use Seat\Services\Jobs\Analytics;

/**
 * Class Base
 * @package Seat\Eveapi\Jobs
 */
abstract class Base implements ShouldQueue
{

    use InteractsWithQueue, Queueable, SerializesModels;

    /**
     * The JobPayloadContainer Instance containing
     * extra payload information.
     *
     * @var \Seat\Eveapi\Helpers\JobPayloadContainer
     */
    protected $job_payload;

    /**
     * The JobTracker instance.
     *
     * @var \Seat\Eveapi\Models\JobTracking
     */
    protected $job_tracker;

    /**
     * Force defining the handle method for the Job worker to call.
     *
     * @return mixed
     */
    abstract public function handle();

    /**
     * Create a new job instance.
     *
     * @param \Seat\Eveapi\Helpers\JobPayloadContainer $job_payload
     */
    public function __construct(JobPayloadContainer $job_payload)
    {

        $this->job_payload = $job_payload;
        $this->job_tracker = null;
    }

    /**
     * Checks the Job Tracking table if the current job
     * has a tracking entry. If not, the job is just
     * deleted.
     *
     * We also check that the EVE API is considered 'UP'
     * before we allow the job to be updated.
     *
     * @return mixed
     */
    public function trackOrDismiss()
    {

        // Match the current job_id with the tracking
        // record we added when queuing the job
        $this->job_tracker = JobTracking::where('job_id',
            $this->job->getJobId())
            ->first();

        // If no tracking record is found, just put
        // the job back in the queue after a few
        // seconds. It could be that the job
        // to add it has not finished yet.
        if (!$this->job_tracker) {

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

        // Check if the EVE API is down. If it is, null
        // the job tracker so that the extended class
        // will stop execution.
        if ($this->isEveApiDown())
            $this->job_tracker = null;

        return;
    }

    /**
     * @param array $data
     */
    public function updateJobStatus(array $data)
    {

        $this->job_tracker->fill($data);
        $this->job_tracker->save();

        return;
    }

    /**
     * Write diagnostic information to the Job Tracker
     *
     * @param \Exception $e
     */
    public function reportJobError(Exception $e)
    {

        // Write an entry to the log file.
        Log::error(
            $this->job_tracker->api . '/' . $this->job_tracker->scope . ' for '
            . $this->job_tracker->owner_id . ' failed with ' . get_class($e)
            . ': ' . $e->getMessage() . '. See the job tracker for more ' .
            'information.');

        // Prepare some useful information about the error.
        $output = 'Last Updater: ' . $this->job_tracker->output . PHP_EOL;
        $output .= PHP_EOL;
        $output .= 'Exception: ' . get_class($e) . PHP_EOL;
        $output .= 'Error Code: ' . $e->getCode() . PHP_EOL;
        $output .= 'Error Message: ' . $e->getMessage() . PHP_EOL;
        $output .= 'File: ' . $e->getFile() . ' - Line: ' . $e->getLine() . PHP_EOL;
        $output .= PHP_EOL;
        $output .= 'Traceback: ' . $e->getTraceAsString() . PHP_EOL;

        $this->updateJobStatus([
            'status' => 'Error',
            'output' => $output
        ]);

        // Analytics. Report only the Exception class and message.
        dispatch((new Analytics((new AnalyticsContainer)
            ->set('type', 'exception')
            ->set('exd', get_class($e) . ':' . $e->getMessage())
            ->set('exf', 1)))
            ->onQueue('medium'));

        return;
    }

    /**
     * Load worker classes from the configuration
     * file based on the 'api' type in the
     * job tracker. This method honors the class
     * definitions in eveapi.config.disabled_workers
     * as well as the key specific disabled_workers.
     *
     * @return mixed
     */
    public function load_workers()
    {

        $type = strtolower($this->job_tracker->api);
        $workers = config('eveapi.workers.' . $type);

        $global_disabled_workers = config(
            'eveapi.config.disabled_workers.' . $type);

        $key_disabled_workers = $this->job_tracker->owner_id == 0 ?
            [] : json_decode(ApiKey::find($this->job_tracker->owner_id)->disabled_calls);

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
     * EVE API Exception. Returns a boolean indicating
     * if the calling job should continue or not.
     *
     * @param \Exception $exception
     *
     * @return bool
     * @throws \Exception
     */
    public function handleApiException(Exception $exception)
    {

        // Get the API Key instance from the Job Payload
        $api_key = $this->job_payload->eve_api_key;

        // Start by allowing the parent job to continue.
        $should_continue = true;

        // No matter what the error, we will increment the
        // Api Error Counter.
        $this->incrementApiErrorCount();

        // Errors from the EVE API should be treated seriously. If
        // these are ignored, one may risk having the calling IP
        // banned entirely. We don't want that, so lets check
        // and act accordingly based on the error code. We also rely
        // entirely on PhealNG to pass us the proper error codes.
        switch ($exception->getCode()) {

            // Invalid contractID something. Probably the
            // most annoying freaking response code that
            // CCP has!
            case 135:
                break;

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
                $api_key->update([
                    'enabled'    => false,
                    'last_error' => $exception->getCode() . ':' . $exception->getMessage()
                ]);
                $should_continue = false;

                break;

            // "Invalid Corporation Key. Key owner does not fullfill role
            // requirements anymore."
            case 220:
                $api_key->update([
                    'enabled'    => false,
                    'last_error' => $exception->getCode() . ':' . $exception->getMessage()
                ]);
                $should_continue = false;

                break;

            // "Illegal page request! Please verify the access granted by the key you are using!."
            case 221:
                // Not 100% sure how to handle this one. This call has no
                // access mask requirement...
                $api_key->update([
                    'last_error' => $exception->getCode() . ':' . $exception->getMessage()
                ]);

                break;

            // "Key has expired. Contact key owner for access renewal."
            case 222:
                $api_key->update([
                    'enabled'    => false,
                    'last_error' => $exception->getCode() . ':' . $exception->getMessage()
                ]);
                $should_continue = false;

                break;

            // "Authentication failure. Legacy API keys can no longer be
            // used. Please create a new key on support.eveonline.com
            // and make sure your application supports Customizable
            // API Keys."
            case 223:
                // The API we are working with is waaaaaay too old.
                $api_key->update([
                    'enabled'    => false,
                    'last_error' => $exception->getCode() . ':' . $exception->getMessage()
                ]);
                $should_continue = false;

                break;

            // "Web site database temporarily disabled."
            case 901:
                $this->markEveApiDown();
                $should_continue = false;

                break;

            // "EVE backend database temporarily disabled.""
            case 902:
                $this->markEveApiDown();
                $should_continue = false;

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
                // Get time of IP ban in minutes, rounded up to the next whole minute
                $time = round((
                        $exception->cached_until_unixtime -
                        $exception->request_time_unixtime) / 60, 0, PHP_ROUND_HALF_UP);
                $this->markEveApiDown($time);
                $should_continue = false;

                break;

            // We got a problem we don't know what to do with, so log
            // and throw the exception so that the can debug it.
            default:
                throw $exception;
                break;

        }

        // Update the Job itself with the error information
        $this->reportJobError($exception);

        return $should_continue;
    }

    /**
     * Attempt to take the appropriate action based on the
     * EVE API Connection Exception.
     *
     * @param $exception
     */
    public function handleConnectionException($exception)
    {

        $this->incrementConnectionErrorCount();

        Log::warning(
            'A connection exception occured to the API server. ' .
            $exception->getCode() . ':' . $exception->getMessage());

        sleep(1);

        return;
    }

    /**
     * Queued jobs can fail outside of the large try/catch block
     * that it is wrapped in. Unfortunately when this happens,
     * the job tracker will never get updated, preventing another
     * job from entering the queue.
     *
     * What really sucks about this is that the actual why gets
     * lots when the `failed()` method is called, meaning we never
     * know what actually went wrong. Hopefully by updating the
     * jon tracker record, we have a time to narrow any possible
     * exceptions down in the global log.
     *
     * @param \Seat\Eveapi\Helpers\JobPayloadContainer $job
     * @param \Exception                               $exception
     */
    public function handleFailedJob(JobPayloadContainer $job, Exception $exception)
    {

        Log::error('A job failure occured in ' . __CLASS__ . '. Marking it as failed.');

        // Try and find the jobtracking entry. Sadly, because the context
        // seems logs in the `failed()` methods, we cant just lookup by
        // job_id :((
        $job_tracker = JobTracking::where('owner_id', $job->owner_id)
            ->where('api', $job->api)
            ->where('scope', $job->scope)
            ->where('status', '<>', 'Error')
            ->first();

        if (!$job_tracker)
            Log::error('Unable to find the job tracking entry for the failed job in ' . __CLASS__);

        $job_tracker->status = 'Error';
        $job_tracker->output = 'An general failure  in ' . __CLASS__ . ' occured. ' .
            'Refer to the logs at ' . Carbon::now()->toDateTimeString() . ' for more ' .
            'information. Details about the exception: ' . PHP_EOL .
            PHP_EOL .
            'Exception: ' . get_class($exception) . PHP_EOL .
            'Error Code: ' . $exception->getCode() . PHP_EOL .
            'Error Message: ' . $exception->getMessage() . PHP_EOL .
            'File: ' . $exception->getFile() . ' - Line: ' . $exception->getLine() . PHP_EOL .
            PHP_EOL .
            'Traceback: ' . $exception->getTraceAsString() . PHP_EOL;

        $job_tracker->save();

        return;

    }

    /**
     * Increment the API Error Count. If we reach the configured
     * threshold then we mark the EVE Api as down for a few
     * minutes
     *
     * @param int $amount
     */
    public function incrementApiErrorCount($amount = 1)
    {

        if (Cache::get(
                config('eveapi.config.cache_keys.api_error_count')) <
            config('eveapi.config.limits.eveapi_errors')
        )
            Cache::increment(
                config('eveapi.config.cache_keys.api_error_count'), $amount);

        if (Cache::get(
                config('eveapi.config.cache_keys.api_error_count')) >=
            config('eveapi.config.limits.eveapi_errors')
        )
            $this->markEveApiDown(10);

        return;

    }

    /**
     * Decrement the Api Error Counter
     *
     * @param int $amount
     */
    public function decrementApiErrorCount($amount = 1)
    {

        if (Cache::get(
                config('eveapi.config.cache_keys.api_error_count')) > 0
        )
            Cache::decrement(
                config('eveapi.config.cache_keys.api_error_count'), $amount);

        return;

    }

    /**
     * Increment the Connection Error Count. If we reach the
     * configured threshold then we mark the EVE Api as
     * down for a few minutes
     *
     * @param int $amount
     */
    public function incrementConnectionErrorCount($amount = 1)
    {

        if (Cache::get(
                config('eveapi.config.cache_keys.connection_error_count')) <
            config('eveapi.config.limits.connection_errors')
        )
            Cache::increment(
                config('eveapi.config.cache_keys.connection_error_count'), $amount);

        if (Cache::get(
                config('eveapi.config.cache_keys.connection_error_count')) >=
            config('eveapi.config.limits.connection_errors')
        )
            $this->markEveApiDown(15);

        return;

    }

    /**
     * Decrement the Connection Error Counter
     *
     * @param int $amount
     */
    public function decrementConnectionErrorCount($amount = 1)
    {

        if (Cache::get(
                config('eveapi.config.cache_keys.connection_error_count')) > 0
        )
            Cache::decrement(
                config('eveapi.config.cache_keys.connection_error_count'), $amount);

        return;

    }

    /**
     * Decrement all the error counters
     *
     * @param int $amount
     */
    public function decrementErrorCounters($amount = 1)
    {

        $this->decrementApiErrorCount($amount);
        $this->decrementConnectionErrorCount($amount);

        return;
    }

    /**
     * Mark the EVE Api as down by setting a key to
     * true in the cache.
     *
     * @param int $minutes
     *
     * @return mixed
     */
    public function markEveApiDown($minutes = 30)
    {

        $down_expiration = Carbon::now()->addMinutes($minutes)
            ->toDateTimeString();

        Cache::put(
            config('eveapi.config.cache_keys.down_until'),
            $down_expiration,
            $minutes);

        Log::warning('Eve Api Marked as down for ' . $minutes . ' minutes');

        return Cache::put(
            config('eveapi.config.cache_keys.down'), true, $minutes);
    }

    /**
     * Check if the EVE Api is considered 'down'
     */
    public function isEveApiDown()
    {

        $down = cache(config('eveapi.config.cache_keys.down'));

        // If the server is down and we have a job that
        // we can update, update it.
        if ($down && $this->job_tracker) {

            $this->job_tracker->status = 'Done';
            $this->job_tracker->output = 'The EVE Api Server is currently down';
            $this->job_tracker->save();
        }

        return $down;

    }

    /**
     * Mark a Job as Done
     */
    public function markAsDone()
    {

        $this->updateJobStatus([
            'status' => 'Done',
            'output' => null
        ]);

        return;
    }

    /**
     * @param \Exception $exception
     */
    public function failed(Exception $exception)
    {

        $this->handleFailedJob($this->job_payload, $exception);

        return;

    }

}
