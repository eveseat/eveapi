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
     * @param $next
     *
     * @throws \Exception
     */
    public function handle($job, $next)
    {
        // bypass control if the class is not related to ESI or is the ESI ping job
        if (is_subclass_of($job, EsiBase::class) && ! ($job instanceof Esi)) {
            logger()->debug('middleware: esistatus: checking for ' . $job->getEndpoint());

            if (! $this->isRouteOnline($job->getEndpoint())) {
                logger()->warning(
                    sprintf('ESI route seems to be unavailable. Job %s has been aborted.',
                        get_class($job)));

                $job->delete();

                return;
            }
        }

        $next($job);
    }

    /**
     * @param $endpoint
     * @return bool
     */
    private function isRouteOnline($endpoint): bool
    {

        $cacheKey = 'esi-route-status:' . $endpoint;

        // Get the latest ESI status.
        $status = cache()->remember($cacheKey, self::ROUTE_STATUS_DURATION, function () use ($endpoint) {
            // Need to probe the status endpoint in order to determine if it is up.
            logger()->debug('middleware: esistatus: probing endpoint ' . $endpoint);
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

        logger()->debug('middleware: esistatus: result for ' . $endpoint . ' is ' . $status);

        // If the status is OK, yay.

        return $status == 'green' or $status == 'yellow';
    }
}
