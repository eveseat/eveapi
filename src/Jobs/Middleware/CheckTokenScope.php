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
 * Class CheckTokenScope.
 *
 * @package Seat\Eveapi\Jobs\Middleware
 */
class CheckTokenScope
{
    /**
     * @param  \Seat\Eveapi\Jobs\EsiBase  $job
     * @param  \Closure  $next
     * @return void
     */
    public function handle(EsiBase $job, Closure $next): void
    {
        // in case the job does not require specific scopes - forward
        if ($job->getScope() == '') {
            logger()->debug(
                sprintf('[Jobs][Middlewares][%s] Check Token Scope -> Bypassed due to unneeded scope to process this job.', $job->job->getJobId()),
                [
                    'fqcn' => get_class($job),
                ]);

            $next($job);

            return;
        }

        // load sso scopes profiles
        $profiles = collect(setting('sso_scopes', true) ?? [
            [
                'id' => 0,
                'name' => 'default',
                'default' => true,
                'scopes' => config('eveapi.scopes', []),
            ],
        ]);

        // in case token related profile is not requiring the job needed scope - log for diagnose and remove it from the queue
        if (! in_array($job->getScope(), $profiles->where('id', $job->getToken()->scopes_profile)->first()->scopes ?? [])) {
            logger()->warning(
                sprintf('[Jobs][Middlewares][%s] Check Token Scope -> Removing job due to required scopes not matching with token related scopes profile.', $job->job->getJobId()),
                [
                    'fqcn' => get_class($job),
                    'character_id' => $job->getToken()->character_id,
                    'scopes_profile' => $job->getToken()->scopes_profile,
                ]);

            $job->delete();

            return;
        }

        // in case token got required scope and job require it - forward
        if (in_array($job->getScope(), $job->getToken()->scopes)) {
            $next($job);

            return;
        } else {
            logger()->warning(
                sprintf('[Jobs][Middlewares][%s] Check Token Scope -> Removing job due to required scopes not matching with token scopes.', $job->job->getJobId()),
                [
                    'fqcn' => get_class($job),
                    'job_scopes' => $job->getScope(),
                    'character_id' => $job->getToken()->character_id,
                ]);            
            $job->delete();
            return;
        }

        // log event otherwise
        logger()->warning(
            sprintf('[Jobs][Middlewares][%s] Check Token Scope -> A job requiring a not granted scope has been queued.', $job->job->getJobId()),
            [
                'fqcn' => get_class($job),
                'required_scope' => $job->getScope(),
                'token_scopes' => $job->getToken()->scopes,
                'token_owner' => $job->getToken()->character_id,
            ]);

        $job->tries = 1;
        $job->fail('A job requiring a not granted scope has been queued.');
    }
}
