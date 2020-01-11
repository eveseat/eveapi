<?php

/*
 * This file is part of SeAT
 *
 * Copyright (C) 2015, 2016, 2017, 2018, 2019, 2020  Leon Jacobs
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

use Seat\Eveapi\Exception\InvalidScopeException;
use Seat\Eveapi\Jobs\EsiBase;

/**
 * Class CheckTokenScope.
 *
 * @package Seat\Eveapi\Jobs\Middleware
 */
class CheckTokenScope
{
    /**
     * @param \Seat\Eveapi\Jobs\EsiBase $job
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

        // otherwise, crash the job
        $job->fail(new InvalidScopeException($job->getScope(), $job->getToken()->scopes));
    }
}
