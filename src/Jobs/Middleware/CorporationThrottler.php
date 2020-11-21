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
 * Class CorporationThrottler.
 *
 * @package Seat\Eveapi\Jobs\Middleware
 */
class CorporationThrottler
{
    /**
     * @param \Seat\Eveapi\Jobs\AbstractCorporationJob $job
     * @param $next
     *
     * @throws \Illuminate\Contracts\Redis\LimiterTimeoutException
     */
    public function handle($job, $next)
    {
        $key = sprintf('corporation-%d', $job->getCorporationId());

        Redis::throttle($key)->block(0)->allow(1)->every(2)->then(function () use ($job, $next) {
            logger()->debug('Job pass corporation rate-limit check and is granted for processing.', [
                'job' => get_class($job),
                'corporation_id' => $job->getCorporationId(),
            ]);

            $next($job);
        }, function () use ($job) {
            logger()->debug('Another job is already processing data for this corporation. Delay job by 2 seconds.', [
                'job' => get_class($job),
                'corporation_id' => $job->getCorporationId(),
            ]);

            $job->release(2);
        });
    }
}
