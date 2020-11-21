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
 * Class EsiTokenThrottler.
 *
 * @package Seat\Eveapi\Jobs\Middleware
 */
class EsiTokenThrottler
{
    /**
     * @param \Seat\Eveapi\Jobs\EsiBase $job
     * @param $next
     *
     * @throws \Illuminate\Contracts\Redis\LimiterTimeoutException
     */
    public function handle($job, $next)
    {
        $key = sprintf('token-%d', $job->getToken()->character_id);

        Redis::throttle($key)->block(0)->allow(1)->every(2)->then(function () use ($job, $next) {
            logger()->debug('Job pass token rate-limit check and is granted for processing.', [
                'job' => get_class($job),
                'token_id' => $job->getToken()->character_id,
            ]);

            $next($job);
        }, function () use ($job) {
            logger()->debug('Another job is already processing data for this token. Delay job by 2 seconds.', [
                'job' => get_class($job),
                'token_id' => $job->getToken()->character_id,
            ]);

            $job->release(2);
        });
    }
}
