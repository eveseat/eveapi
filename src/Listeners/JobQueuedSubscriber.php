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

use Illuminate\Queue\Events\JobQueued;
use Seat\Eveapi\Events\EsiJobQueued;
use Seat\Eveapi\Jobs\AbstractAllianceJob;
use Seat\Eveapi\Jobs\AbstractCharacterJob;
use Seat\Eveapi\Jobs\AbstractCorporationJob;
use Throwable;

/**
 * Class EsiJobQueued.
 *
 * @package Seat\Eveapi\Listeners
 */
class JobQueuedSubscriber
{
    /**
     * @param  \Illuminate\Queue\Events\JobQueued  $event
     */
    public function handle(JobQueued $event)
    {
        try {
            if ($event->job instanceof AbstractCharacterJob || $event->job instanceof AbstractCorporationJob || $event->job instanceof AbstractAllianceJob) {
                logger()->debug('An ESI Entity job has been queued.', [
                    'class' => get_class($event->job),
                    'scope' => $event->job->getJobScope(),
                    'entity' => $event->job->getEntityId(),
                ]);

                // in case job was processing - trigger an event which mark it as working
                EsiJobQueued::dispatch(get_class($event->job), $event->job->displayName(), $event->job->getJobScope(), $event->job->getEntityId());
            }
        } catch (Throwable $exception) {
            logger()->error($exception->getMessage());
        }
    }
}
