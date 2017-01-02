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

use Pheal\Exceptions\AccessException;
use Pheal\Exceptions\APIException;
use Pheal\Exceptions\ConnectionException;
use Pheal\Exceptions\PhealException;

/**
 * Class UpdateAuthenticated.
 * @package Seat\Eveapi\Jobs
 */
class UpdateAuthenticated extends Base
{
    /**
     * Execute the job.
     */
    public function handle()
    {

        // Find the tracking record for this job. If there
        // is none, simply return and do nothing.
        if (! $this->trackOrDismiss())
            return;

        // Do the update work and catch any errors
        // that may come of it.
        $this->updateJobStatus(['status' => 'Working']);

        // Load the workers for this update job.
        $workers = $this->load_workers();

        // Write a joblog entry.
        $this->writeInfoJobLog('Started API Updates with ' . $workers->count() . ' workers.');

        // Take note of when the updater started.
        $job_start = microtime(true);

        // Attempt to run the Updaters based on the
        // type of key we are working with.
        foreach ($workers as $worker) {

            try {

                // Update the job status
                $this->updateJobStatus([
                    'output' => 'Processing: ' . class_basename($worker),
                ]);

                // Write a joblog entry
                $this->writeInfoJobLog('Started Worker: ' . class_basename($worker));

                // Keep in mind when we started the work.
                $worker_start = microtime(true);

                // Perform the update for the specific worker.
                (new $worker)->setApi($this->job_payload->eve_api_key)->call();
                $this->decrementErrorCounters();

                $this->writeInfoJobLog(class_basename($worker) .
                    ' took ' . number_format(microtime(true) - $worker_start, 2) . 's to complete');

            } catch (AccessException $e) {

                // The EveApiAccess Class will throw this and log the exception.
                $this->writeErrorJobLog('An AccessException occured while processing ' .
                    class_basename($worker) . '. This normally means the key does not have access.');

                continue;

            } catch (APIException $e) {

                $this->writeErrorJobLog('An APIException occured while processing ' .
                    class_basename($worker) . '. The exception error was: ' . $e->getMessage());

                // If we should not continue, simply return.
                if (! $this->handleApiException($this->job_payload->eve_api_key, $e))
                    return;

                continue;

            } catch (ConnectionException $e) {

                $this->writeErrorJobLog('A ConnectionException occured while processing ' .
                    class_basename($worker) . '. The exception error was: ' . $e->getMessage());

                $this->handleConnectionException($e);
                continue;

            } catch (PhealException $e) {

                $this->writeErrorJobLog('A PhealException occured while processing ' .
                    class_basename($worker) . '. The exception error was: ' . $e->getMessage());

                // Handle the tyipcal XML related exceptions as a
                // connection exception for now.
                $this->handleConnectionException($e);

                continue;
            }

        } // Foreach worker

        // Note how long it too to run the whole update.
        $this->writeInfoJobLog('The full update run took ' .
            number_format(microtime(true) - $job_start, 2) . 's to complete');

        // Mark the Job as complete.
        $this->updateJobStatus([
            'status' => 'Done',
            'output' => null,
        ]);

    }
}
