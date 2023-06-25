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

namespace Seat\Eveapi\Jobs\Middleware;

use Closure;
use Seat\Eseye\Exceptions\RequestFailedException;
use Seat\Eveapi\Jobs\EsiBase;
use Seat\Eveapi\Jobs\Status\Esi;
use Seat\Services\Contracts\EsiClient;

/**
 * Class CheckEsiRouteStatus.
 *
 * @package Seat\Eveapi\Jobs\Middleware
 */
class CheckEsiRouteStatus
{

    const ROUTE_STATUS_DURATION = 300;

    /**
     * @param  \Seat\Eveapi\Jobs\EsiBase  $job
     * @param  \Closure  $next
     * @return void
     */
    public function handle(EsiBase $job, Closure $next): void
    {
        // bypass control if the class is not related to ESI or is the ESI ping job
        if ($job instanceof Esi) {
            logger()->debug(
                sprintf('[Jobs][Middlewares][%s] Check ESI Route Status -> Bypassed due to Esi job instance.', $job->job->getJobId()),
                [
                    'fqcn' => get_class($job),
                    'endpoint' => $job->getEndpoint(),
                ]);

            $next($job);

            return;
        }

        logger()->debug(
            sprintf('[Jobs][Middlewares][%s] Check ESI Route Status -> Checking endpoint health.', $job->job->getJobId()),
            [
                'fqcn' => get_class($job),
                'endpoint' => $job->getEndpoint(),
            ]);

        if (! $this->isRouteOnline($job->getEndpoint())) {
            logger()->warning(
                sprintf('[Jobs][Middlewares][%s] Check ESI Route Status -> Endpoint seems to be unavailable, aborting job.', $job->job->getJobId()),
                [
                    'fqcn' => get_class($job),
                    'endpoint' => $job->getEndpoint(),
                ]);

            return;
        }

        $next($job);
    }

    /**
     * @param  string  $endpoint
     * @return bool
     */
    private function isRouteOnline(string $endpoint): bool
    {

        $cacheKey = 'esi-route-status:' . $endpoint;

        // Get the latest ESI status.
        $status = cache()->remember($cacheKey, self::ROUTE_STATUS_DURATION, function () use ($endpoint) {

            // Need to probe the status endpoint in order to determine if it is up.
            try {
                $client = app()->make(EsiClient::class);
                $client->setVersion('');
                $client->setQueryString(['version' => 'latest']);
                $response = $client->invoke('get', '/status.json');

                $data = $response->getBody();

                foreach($data as $path) {
                    if ($path->route == $endpoint) {
                        return $path->status ?? 'invalid';
                    }
                }

                return 'missing';

           } catch (RequestFailedException $e) {
                return 'inaccessible';
           }

        });

        logger()->debug('[Jobs][Middlewares] Check ESI Route Status -> Probing ESI endpoints.', [
            'endpoint' => $endpoint,
            'status' => $status
        ]);

        // If the status is OK, yay.

        return $status == 'green' or $status == 'yellow';
    }
}
