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

/**
 * Class UpdatePublic.
 * @package Seat\Eveapi\Jobs
 */
class UpdateEsi extends Base
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

        // Attempt to run the Updaters based on the
        // type of key we are working with.
        foreach ($this->load_workers() as $worker) {

            try {

                $this->updateJobStatus([
                    'output' => 'Processing: ' . class_basename($worker),
                ]);

                // Perform the update
                (new $worker)->call();
                $this->decrementErrorCounters();

            } catch (ConnectionException $e) {

                $this->handleConnectionException($e);
                continue;

            } catch (\Exception $e) {
                if (!$this->handleApiException($e))
                    return;

                continue;
            }

        } // Foreach worker

        // Mark the Job as complete.
        $this->updateJobStatus([
            'status' => 'Done',
            'output' => null,
        ]);

    }
}
