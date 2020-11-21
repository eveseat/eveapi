<?php

/*
 * This file is part of SeAT
 *
 * Copyright (C) 2015 to 2020 Leon Jacobs
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

use Illuminate\Support\Facades\Redis;

/**
 * Class KillmailRateLimit.
 *
 * @package Seat\Eveapi\Jobs\Middleware
 */
class KillmailRateLimit
{
    /**
     * Allow a specific killmail to be processed only once.
     *
     * @param \Seat\Eveapi\Jobs\Killmails\Detail $job
     * @param $next
     */
    public function handle($job, $next)
    {
        $key = sprintf('killmail-%d', $job->killmail_id);

        Redis::throttle($key)->block(0)->allow(1)->every(2)->then(function () use ($job, $next) {
            logger()->debug('Killmail pass rate-limit check and is granted for processing.', [
                'killmail_id' => $job->killmail_id,
            ]);

            $next($job);
        }, function () use ($job) {
            logger()->debug('Killmail is already handled by another job. Kill this job request.', [
                'killmail_id' => $job->killmail_id,
            ]);

            $job->delete();
        });
    }
}
