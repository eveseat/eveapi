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

use Cache;
use Carbon\Carbon;
use Exception;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Seat\Eveapi\Helpers\JobPayloadContainer;
use Seat\Eveapi\Models\Eve\ApiKey;
use Seat\Eveapi\Models\JobLog;
use Seat\Eveapi\Models\JobTracking;
use Seat\Services\Helpers\AnalyticsContainer;
use Seat\Services\Jobs\Analytics;

/**
 * Class Base.
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
     * Create a new job instance.
     *
     * @param \Seat\Eveapi\Helpers\JobPayloadContainer $job_payload
     */
    public function __construct(JobPayloadContainer $job_payload)
    {

        $this->job_payload = $job_payload;
    }

    /**
     * Force defining the handle method for the Job worker to call.
     *
     * @return mixed
     */
    abstract public function handle();

    /**
     * Checks the Job Tracking table if the current job
     * has a tracking entry. If not, the job is just
     * deleted.
     *
     * We also check that the EVE API is considered 'UP'
     * before we allow the job to be updated.
     *
     * @return bool
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
        if (! $this->job_tracker) {

            // Check that we have not come by this logic
            // for like the 10th time now.
            if ($this->attempts() < 10) {

                // Add the job back into the queue and wait
                // for 2 seconds before releasing it.
                $this->release(2);

                return false;
            }

            // Remove yourself from the queue
            logger()->error(
                'Error finding a JobTracker for job ' . $this->job->getJobID());
            $this->delete();

            return false;
        }

        // Check if the EVE API is down.
        if ($this->isEveApiDown())
            return false;

        return true;
    }

    /**
     * Check if the EVE Api is considered 'down'.
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
     * Retreive the worker classes to run.
     *
     * Classes can be defined in the Eve\ApiKey models
     * api_call_constraints column, or globally in the
     * api_constraint key.
     *
     * Class constraints are honoured first based on the
     * key specific configuration, and then the global
     * configuration as a fallback. If neither have a
     * definition, then all the Classes will be run.
     *
     * @return mixed
     */
    public function load_workers()
    {

        // Check the type of updater running.
        $updater_type = strtolower($this->job_tracker->api);

        // If needed, get the constraints defined in the API key.
        // Calls such EVE Universe Info and Server Status wont have
        // an API key to query for this. Those can just be skipped.
        if ($this->job_payload->eve_api_key) {

            // Get the constraints saved to the key.
            if ($classes = $this->extractWorkerClasses(
                $updater_type, $this->job_payload->eve_api_key->api_call_constraints)
            ) {

                $this->writeInfoJobLog('API Key constraints exist. Loading ' .
                    $classes->count() . ' workers');

                return $classes;
            }
        }

        // Check the global constraints from Settings::Seat
        if ($classes = $this->extractWorkerClasses(
            $updater_type, json_decode(setting('api_constraint', true), true))
        ) {

            $this->writeInfoJobLog('Global constraints exist. Loading ' .
                $classes->count() . ' workers');

            return $classes;
        }

        $this->writeInfoJobLog('No constraints exist. Loading all workers');

        // No constraints are defined, so return all of the
        // workers appliable to this updater.
        return collect(config('eveapi.worker_groups.' . $updater_type))
            ->flatten();

    }

    /**
     * Take an updater type and constraints array and determine
     * if there are any affective constraints applied. If so,
     * the worker classes in those constraint groups are returned.
     *
     * @param string     $type
     * @param array|null $constraints
     *
     * @return static
     */
    private function extractWorkerClasses(string $type, $constraints)
    {

        // If there are constraints for this update type load those workers.
        if ($constraints &&
            array_key_exists($type, $constraints) &&
            count($constraints[$type]) > 0
        ) {

            // Get the constraints that we can use in the closure
            // that follows.
            $constraints = $constraints[$type];

            return collect(config('eveapi.worker_groups.' . $type))
                ->filter(function ($_, $key) use ($constraints) {

                    // Return if the updater category is in the constraints.
                    return in_array($key, $constraints);
                })
                ->flatten();
        }

    }

    /**
     * @param string $message
     */
    public function writeInfoJobLog(string $message)
    {

        $this->writeJobLog('info', $message);
    }

    /**
     * @param string $type
     * @param string $message
     */
    public function writeJobLog(string $type, string $message)
    {

        // Ensure that the joblog is enabled first
        if (! config('eveapi.config.enable_joblog'))
            return;

        if ($this->job_payload->eve_api_key)
            $this->job_payload->eve_api_key->job_logs()->save(
                new JobLog([
                    'type'    => $type,
                    'message' => $message,
                ])
            );

    }

    /**
     * Decrement all the error counters.
     *
     * @param int $amount
     */
    public function decrementErrorCounters(int $amount = 1)
    {

        $this->decrementApiErrorCount($amount);
        $this->decrementConnectionErrorCount($amount);

    }

    /**
     * Decrement the Api Error Counter.
     *
     * @param int $amount
     */
    public function decrementApiErrorCount(int $amount = 1)
    {

        // Get the key names to use in the cache
        $api_error_count = config('eveapi.config.cache_keys.api_error_count');

        if (cache($api_error_count) > 0)
            Cache::decrement($api_error_count, $amount);

    }

    /**
     * Decrement the Connection Error Counter.
     *
     * @param int $amount
     */
    public function decrementConnectionErrorCount(int $amount = 1)
    {

        // Get the key names to use in the cache
        $connection_error_count = config('eveapi.config.cache_keys.connection_error_count');

        if (cache($connection_error_count) > 0)
            Cache::decrement($connection_error_count, $amount);

    }

    /**
     * Handle an exception that can be thrown by a job.
     *
     * This is the failed method that Laravel itself will call
     * when a jobs `handle` method throws any uncaught exception.
     *
     * @param \Exception $exception
     */
    public function failed(Exception $exception)
    {

        logger()->error(
            'A worker error occured. The exception thrown was ' .
            $exception->getMessage() . ' in file ' . $exception->getFile() .
            ' on line ' . $exception->getLine()
        );

        // Try and find the jobtracking entry. Sadly, because the context
        // seems lost in the `failed()` methods, we cant just lookup by job_id :(
        $job_tracker = JobTracking::where('owner_id', $this->job_payload->owner_id)
            ->where('api', $this->job_payload->api)
            ->where('scope', $this->job_payload->scope)
            ->where('status', '<>', 'Error')
            ->first();

        if ($job_tracker) {

            // Prepare some useful information about the error.
            $output = 'Last Updater: ' . $job_tracker->output . PHP_EOL;
            $output .= PHP_EOL;
            $output .= 'Exception       : ' . get_class($exception) . PHP_EOL;
            $output .= 'Error Code      : ' . $exception->getCode() . PHP_EOL;
            $output .= 'Error Message   : ' . $exception->getMessage() . PHP_EOL;
            $output .= 'File            : ' . $exception->getFile() . PHP_EOL;
            $output .= 'Line            : ' . $exception->getLine() . PHP_EOL;
            $output .= PHP_EOL;
            $output .= 'Traceback: ' . PHP_EOL;
            $output .= $exception->getTraceAsString() . PHP_EOL;

            $job_tracker->fill([
                'status' => 'Error',
                'output' => $output,
            ])->save();

        }

        // Analytics. Report only the Exception class and message.
        dispatch((new Analytics((new AnalyticsContainer)
            ->set('type', 'exception')
            ->set('exd', get_class($exception) . ':' . $exception->getMessage())
            ->set('exf', 1)))
            ->onQueue('medium'));

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
                $this->writeErrorJobLog('Checking if key should be disabled due to response code 212');

                $this->disableKeyIfGracePeriodReached(
                    $api_key, $exception->getCode() . ':' . $exception->getMessage());

                $should_continue = false;

                break;

            // "Invalid Corporation Key. Key owner does not fullfill role
            // requirements anymore."
            case 220:
                $this->writeErrorJobLog('Checking if key should be disabled due to response code 220');

                $this->disableKeyIfGracePeriodReached(
                    $api_key, $exception->getCode() . ':' . $exception->getMessage());

                $should_continue = false;

                break;

            // "Illegal page request! Please verify the access granted by the key you are using!."
            case 221:
                // Not 100% sure how to handle this one. This call has no
                // access mask requirement...
                $this->writeErrorJobLog('Illegal page request occured');
                $api_key->update([
                    'last_error' => $exception->getCode() . ':' . $exception->getMessage(),
                ]);

                break;

            // "Key has expired. Contact key owner for access renewal."
            case 222:
                $this->writeErrorJobLog('Checking if key should be disabled due to response code 222');

                $this->disableKeyIfGracePeriodReached(
                    $api_key, $exception->getCode() . ':' . $exception->getMessage());

                $should_continue = false;

                break;

            // "Authentication failure. Legacy API keys can no longer be
            // used. Please create a new key on support.eveonline.com
            // and make sure your application supports Customizable
            // API Keys."
            case 223:
                // The API we are working with is waaaaaay too old.
                $this->writeErrorJobLog('Checking if key should be disabled due to response code 223');

                $this->disableKeyIfGracePeriodReached(
                    $api_key, $exception->getCode() . ':' . $exception->getMessage());

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
     * Increment the API Error Count. If we reach the configured
     * threshold then we mark the EVE Api as down for a few
     * minutes.
     *
     * @param int $amount
     */
    public function incrementApiErrorCount(int $amount = 1)
    {

        // Get the key names to use in the cache
        $api_error_count = config('eveapi.config.cache_keys.api_error_count');
        $api_error_limit = config('eveapi.config.limits.eveapi_errors');

        // Update the cache by $amount
        if (cache($api_error_count) < $api_error_limit)
            Cache::increment($api_error_count, $amount);

        // If we have hit the error limit, mark the api as down
        if (cache($api_error_count) >= $api_error_limit)
            $this->markEveApiDown(10);

    }

    /**
     * Mark the EVE Api as down by setting a key to
     * true in the cache.
     *
     * @param int $minutes
     *
     * @return mixed
     */
    public function markEveApiDown(int $minutes = 30)
    {

        $down_expiration = Carbon::now()->addMinutes($minutes)
            ->toDateTimeString();

        Cache::put(
            config('eveapi.config.cache_keys.down_until'),
            $down_expiration,
            $minutes);

        logger()->warning('Eve Api Marked as down for ' . $minutes . ' minutes');

        return Cache::put(
            config('eveapi.config.cache_keys.down'), true, $minutes);
    }

    /**
     * @param string $message
     */
    public function writeErrorJobLog(string $message)
    {

        $this->writeJobLog('error', $message);
    }

    /**
     * Checks an API keys 'grace period' and disables it if the
     * number of errors has passed the grace count.
     *
     * @param \Seat\Eveapi\Models\Eve\ApiKey $api_key
     * @param string                         $message
     */
    public function disableKeyIfGracePeriodReached(ApiKey $api_key, string $message)
    {

        // Determine what the cache key should be.
        $cache_key = 'eveapi_api_error_count. ' . $api_key->key_id;

        // Get the current value, or default to 0 if its not present.
        $count = Cache::get($cache_key, 0);

        // Increment the count by one and place it in the cache for 6 hours.
        $count += 1;
        Cache::put($cache_key, $count, 60 * 6);

        $this->writeInfoJobLog('The grace error count is now ' . $count . '. ' .
            'The key will disable in ' . (config('eveapi.config.error_grace') - $count) .
            ' errors');

        // If we have passed the grace count, disable the key.
        if ($count >= config('eveapi.config.error_grace')) {

            $api_key->update([
                'enabled'    => false,
                'last_error' => $message,
            ]);

            $this->writeInfoJobLog('Api Key disabled as it has reached the grace error count of ' .
                config('eveapi.config.error_grace'));
        }

    }

    /**
     * Write diagnostic information to the Job Tracker.
     *
     * @param \Exception $exception
     */
    public function reportJobError(Exception $exception)
    {

        // Write an entry to the log file.
        logger()->error(
            $this->job_tracker->api . '/' . $this->job_tracker->scope . ' for '
            . $this->job_tracker->owner_id . ' failed with ' . get_class($exception)
            . ': ' . $exception->getMessage() . '. See the job tracker for more ' .
            'information.');

        // Prepare some useful information about the error.
        $output = 'Last Updater: ' . $this->job_tracker->output . PHP_EOL;
        $output .= PHP_EOL;
        $output .= 'Exception       : ' . get_class($exception) . PHP_EOL;
        $output .= 'Error Code      : ' . $exception->getCode() . PHP_EOL;
        $output .= 'Error Message   : ' . $exception->getMessage() . PHP_EOL;
        $output .= 'File            : ' . $exception->getFile() . PHP_EOL;
        $output .= 'Line            : ' . $exception->getLine() . PHP_EOL;
        $output .= PHP_EOL;
        $output .= 'Traceback: ' . PHP_EOL;
        $output .= $exception->getTraceAsString() . PHP_EOL;

        $this->updateJobStatus([
            'status' => 'Error',
            'output' => $output,
        ]);

        // Analytics. Report only the Exception class and message.
        dispatch((new Analytics((new AnalyticsContainer)
            ->set('type', 'exception')
            ->set('exd', get_class($exception) . ':' . $exception->getMessage())
            ->set('exf', 1)))
            ->onQueue('medium'));

    }

    /**
     * Update the JobTracker with a new status.
     *
     * @param array $data
     */
    public function updateJobStatus(array $data)
    {

        $this->job_tracker->fill($data);
        $this->job_tracker->save();

    }

    /**
     * Mark a Job as Done.
     */
    public function markAsDone()
    {

        $this->updateJobStatus([
            'status' => 'Done',
            'output' => null,
        ]);

    }

    /**
     * Attempt to take the appropriate action based on the
     * EVE API Connection Exception.
     *
     * @param \Exception $exception
     */
    public function handleConnectionException(Exception $exception)
    {

        $this->incrementConnectionErrorCount();

        logger()->warning(
            'A connection exception occured to the API server. ' .
            $exception->getCode() . ':' . $exception->getMessage());

        sleep(1);

    }

    /**
     * Increment the Connection Error Count. If we reach the
     * configured threshold then we mark the EVE Api as
     * down for a few minutes.
     *
     * @param int $amount
     */
    public function incrementConnectionErrorCount(int $amount = 1)
    {

        // Get the key names to use in the cache
        $connection_error_count = config('eveapi.config.cache_keys.connection_error_count');
        $connection_error_limit = config('eveapi.config.limits.connection_errors');

        // Increment the error count.
        if (cache($connection_error_count) < $connection_error_limit)
            Cache::increment($connection_error_count, $amount);

        // If needed, mark the API down for a few minutes.
        if (cache($connection_error_count) >= $connection_error_limit)
            $this->markEveApiDown(15);

    }
}
