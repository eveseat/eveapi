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

/**
 * Class CheckEsiRateLimit.
 *
 * @package Seat\Eveapi\Jobs\Middleware
 */
class CheckEsiRateLimit
{
    /**
     * @param  \Seat\Eveapi\Jobs\EsiBase  $job
     * @param  \Closure  $next
     * @return void
     */
    public function handle(EsiBase $job, Closure $next): void
    {
        // in case ESI limit has been reached, delay the job
        if ($this->isEsiRateLimitReached($job)) {
            logger()->warning(
                sprintf('[Jobs][Middlewares][%s] ESI Throttler -> Rate Limit has been reached.', $job->job->getJobId()),
                [
                    'fqcn' => get_class($job),
                    'delay' => $job::RATE_LIMIT_DURATION,
                ]);

            $job->release($job::RATE_LIMIT_DURATION);

            return;
        }

        $next($job);
    }

    /**
     * @param  \Seat\Eveapi\Jobs\EsiBase  $job
     * @return bool
     */
    private function isEsiRateLimitReached(EsiBase $job): bool
    {
        $current = cache()->get($job::RATE_LIMIT_KEY) ?: 0;

        logger()->debug(
            sprintf('[Jobs][Middlewares][%s] ESI Throttler -> Retrieve current rate limit status.', $job->job->getJobId()),
            [
                'fqcn' => get_class($job),
                'current' => $current,
                'limit' => $job::RATE_LIMIT,
            ]);

        return $current >= $job::RATE_LIMIT;
    }
}
