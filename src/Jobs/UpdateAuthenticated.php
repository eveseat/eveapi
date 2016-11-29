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

use Pheal\Exceptions\AccessException;
use Pheal\Exceptions\APIException;
use Pheal\Exceptions\ConnectionException;
use Pheal\Exceptions\PhealException;

/**
 * Class UpdateAuthenticated
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
        if (!$this->trackOrDismiss())
            return;

        // Do the update work and catch any errors
        // that may come of it.
        $this->updateJobStatus(['status' => 'Working']);

        // Attempt to run the Updaters based on the
        // type of key we are working with.
        foreach ($this->load_workers() as $worker) {

            try {

                $this->updateJobStatus([
                    'output' => 'Processing: ' . class_basename($worker)
                ]);

                // Perform the update for the specific worker.
                (new $worker)->setApi($this->job_payload->eve_api_key)->call();
                $this->decrementErrorCounters();

            } catch (AccessException $e) {

                // The EveApiAccess Class will throw this and log the exception.
                continue;

            } catch (APIException $e) {

                // If we should not continue, simply return.
                if (!$this->handleApiException($this->job_payload->eve_api_key, $e))
                    return;

                continue;

            } catch (ConnectionException $e) {

                $this->handleConnectionException($e);
                continue;

            } catch (PhealException $e) {

                // Handle the tyipcal XML related exceptions as a
                // connection exception for now.
                $this->handleConnectionException($e);
                continue;
            }

        } // Foreach worker

        // Mark the Job as complete.
        $this->updateJobStatus([
            'status' => 'Done',
            'output' => null
        ]);

    }

}
