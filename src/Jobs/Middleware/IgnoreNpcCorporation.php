<?php

/*
 * This file is part of SeAT
 *
 * Copyright (C) 2015 to 2021 Leon Jacobs
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

use Seat\Eveapi\Jobs\EsiBase;

/**
 * Class IgnoreNpcCorporation.
 *
 * @package Seat\Eveapi\Jobs\Middleware
 */
class IgnoreNpcCorporation
{
    /**
     * @param  \Seat\Eveapi\Jobs\EsiBase  $job
     * @param $next
     *
     * @throws \Exception
     */
    public function handle($job, $next)
    {
        // in case the job is not ESI related - bypass this check
        if (! is_subclass_of($job, EsiBase::class)) {
            $next($job);

            return;
        }

        // in case the job is not targeting corporations - bypass this check
        if (in_array('corporation', $job->tags()) && $this->isNPCCorporation($job)) {
            $job->delete();

            return;
        }

        $next($job);
    }

    /**
     * Determine if the current corporation ID is in NPC corporation range.
     *
     * @param  \Seat\Eveapi\Jobs\EsiBase  $job
     * @return bool
     */
    private function isNPCCorporation(EsiBase $job): bool
    {

        // ID range references:
        //  https://gist.github.com/a-tal/5ff5199fdbeb745b77cb633b7f4400bb
        return 1000000 <= $job->getCorporationId() && $job->getCorporationId() <= 2000000;
    }
}
