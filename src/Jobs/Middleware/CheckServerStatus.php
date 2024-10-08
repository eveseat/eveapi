<?php

/*
 * This file is part of SeAT
 *
 * Copyright (C) 2015 to present Leon Jacobs
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

namespace Seat\Eveapi\Jobs\Middleware;

use Closure;
use Seat\Eveapi\Jobs\EsiBase;
use Seat\Eveapi\Models\Status\ServerStatus;

/**
 * Class CheckServerStatus.
 *
 * @package Seat\Eveapi\Jobs\Middleware
 */
class CheckServerStatus
{
    /**
     * @param  \Seat\Eveapi\Jobs\EsiBase  $job
     * @param  \Closure  $next
     * @return void
     */
    public function handle(EsiBase $job, Closure $next): void
    {
        // TQ seems to be down - delay
        if (! $this->isEveOnline()) {

            logger()->warning(
                sprintf('[Jobs][Middlewares][%s] Check Server Status -> EVE Online server seems to be unreachable, aborting job.', $job->job->getJobId()),
                [
                    'fqcn' => get_class($job),
                ]);

            return;
        }

        $next($job);
    }

    /**
     * @return bool
     */
    private function isEveOnline(): bool
    {
        // get the latest EVE Online server status.
        $status = cache()->remember('eve_db_status', 60, function () {
            return ServerStatus::latest()->first();
        });

        // if we don't have a status yet, assume server is down.
        if (! $status) return false;

        // if the data is too old, return false by default.
        // not being able to retrieve server status could be
        // indicative of many other problems.
        if ($status->created_at->lte(carbon('now')->subMinutes(10)))
            return false;

        return true;
    }
}
