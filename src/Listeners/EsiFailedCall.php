<?php

/*
 * This file is part of SeAT
 *
 * Copyright (C) 2015 to 2022 Leon Jacobs
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

namespace Seat\Eveapi\Listeners;

use Illuminate\Queue\Events\JobExceptionOccurred;
use Seat\Eseye\Exceptions\RequestFailedException;
use Seat\Eveapi\Events\EsiJobFailed;
use Seat\Eveapi\Exception\PermanentInvalidTokenException;
use Seat\Eveapi\Exception\UnavailableEveServersException;
use Seat\Eveapi\Jobs\AbstractAllianceJob;
use Seat\Eveapi\Jobs\AbstractCharacterJob;
use Seat\Eveapi\Jobs\AbstractCorporationJob;
use Throwable;

/**
 * Class EsiFailedCall.
 *
 * @package Seat\Eveapi\Listeners
 */
class EsiFailedCall
{
    /**
     * @param  \Illuminate\Queue\Events\JobExceptionOccurred  $event
     */
    public function handle(JobExceptionOccurred $event)
    {
        try {
            $job_class = unserialize($event->job->payload()['data']['command']);

            // if esi tell us that used token is permanently invalid
            // remove it from the system and mark job as failed.
            if ($event->exception instanceof PermanentInvalidTokenException) {
                $job_class->getToken()->delete();

                $event->job->fail($event->exception);
            }

            // if esi tell us that Tranquility was unavailable, update its local status
            // so middlewares will prevent further jobs to be processed.
            if ($event->exception instanceof UnavailableEveServersException) {
                cache()->remember('eve_db_status', 60, function () {
                    return null;
                });
            }

            // if esi tell us our request was invalid, mark the job as failed.
            if ($event->exception instanceof RequestFailedException) {
                if ($event->exception->getCode() >= 400 && $event->exception->getCode() < 500) {
                    $event->job->fail($event->exception);
                }
            }

            if ($job_class instanceof AbstractCharacterJob || $job_class instanceof AbstractCorporationJob || $job_class instanceof AbstractAllianceJob) {
                EsiJobFailed::dispatch(get_class($job_class), $job_class->displayName(), $job_class->getJobScope(), $job_class->getEntityId());
            }
        } catch (Throwable $exception) {
            logger()->error($exception->getMessage());
        }

        logger()->debug('An exception occurred while processing an ESI job.', [
            'connection' => $event->connectionName,
            'job' => $event->job,
            'exception' => $event->exception,
        ]);
    }
}
