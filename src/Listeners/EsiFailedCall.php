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

use Exception;
use Illuminate\Queue\Events\JobExceptionOccurred;
use Seat\Eseye\Exceptions\RequestFailedException;
use Seat\Eveapi\Exception\PermanentInvalidTokenException;
use Seat\Eveapi\Exception\UnavailableEveServersException;

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
            // if esi tell us that used token is permanently invalid
            // remove it from the system and mark job as failed.
            if ($event->exception instanceof PermanentInvalidTokenException) {
                $esi_job = unserialize($event->job->payload()['data']['command']);
                $esi_job->getToken()->delete();

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
        } catch (Exception $exception) {
            logger()->error($exception->getMessage());
        }

        logger()->debug('[Jobs][Events] An exception occurred while processing an ESI job.', [
            'connection' => $event->connectionName,
            'fqcn' => $event->job,
            'exception' => $event->exception,
        ]);
    }
}
