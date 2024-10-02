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
use Seat\Eveapi\Jobs\AbstractAuthCorporationJob;

/**
 * Class IgnoreNpcCorporation.
 *
 * @package Seat\Eveapi\Jobs\Middleware
 */
class IgnoreNpcCorporation
{
    /**
     * @param  \Seat\Eveapi\Jobs\AbstractAuthCorporationJob  $job
     * @param  \Closure  $next
     * @return void
     */
    public function handle(AbstractAuthCorporationJob $job, Closure $next): void
    {
        // in case the job is not targeting corporations - bypass this check
        if (in_array('corporation', $job->tags()) && $this->isNPCCorporation($job)) {
            logger()->debug(
                sprintf('[Jobs][Middlewares][%s] Check Corporation Type -> Removing job due to NPC related corporation.', $job->job->getJobId()),
                [
                    'fqcn' => get_class($job),
                    'corporation_id' => $job->getCorporationId(),
                ]);

            $job->delete();

            return;
        }

        $next($job);
    }

    /**
     * Determine if the current corporation ID is in NPC corporation range.
     *
     * @param  \Seat\Eveapi\Jobs\AbstractAuthCorporationJob  $job
     * @return bool
     */
    private function isNPCCorporation(AbstractAuthCorporationJob $job): bool
    {

        // ID range references:
        //  https://gist.github.com/a-tal/5ff5199fdbeb745b77cb633b7f4400bb
        return 1000000 <= $job->getCorporationId() && $job->getCorporationId() <= 2000000;
    }
}
