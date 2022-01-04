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

use Seat\Eveapi\Jobs\EsiBase;

/**
 * Class CheckTokenScope.
 *
 * @package Seat\Eveapi\Jobs\Middleware
 */
class CheckTokenScope
{
    /**
     * @param  \Seat\Eveapi\Jobs\EsiBase  $job
     * @param $next
     */
    public function handle($job, $next)
    {
        // in case the job is not related to ESI - bypass this check
        if (! is_subclass_of($job, EsiBase::class)) {
            $next($job);

            return;
        }

        // in case the job does not required specific scopes or token got required scope - forward
        if ($job->getScope() == '' || in_array($job->getScope(), $job->getToken()->scopes)) {
            $next($job);

            return;
        }

        // log event otherwise
        logger()->warning('A job requiring a not granted scope has been queued.', [
            'Job' => get_class($job),
            'Required scope' => $job->getScope(),
            'Token scopes' => $job->getToken()->scopes,
            'Token owner' => $job->getToken()->character_id,
        ]);
    }
}
