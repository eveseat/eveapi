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
use Seat\Eveapi\Models\Character\CharacterRole;
use Seat\Eveapi\Models\RefreshToken;

/**
 * Class RequireCorporationRole.
 *
 * @package Seat\Eveapi\Jobs\Middleware
 */
class RequireCorporationRole
{
    /**
     * @param  \Seat\Eveapi\Jobs\AbstractAuthCorporationJob  $job
     * @param  \Closure  $next
     * @return void
     */
    public function handle(EsiBase $job, Closure $next): void
    {
        // in case no roles is required to process the job - bypass this check
        if (empty($job->getRoles())) {
            logger()->debug(
                sprintf('[Jobs][Middlewares][%s] Check Corporation Role -> Bypassed due to unneeded role to process this job.', $job->job->getJobId()),
                [
                    'fqcn' => get_class($job),
                ]);

            $next($job);

            return;
        }

        // in case attached character got roles required to execute the job - bypass this check
        if ($this->isCorpCharacterWithRoles($job)) {
            $next($job);

            return;
        }

        logger()->warning('[Jobs][Middlewares][%s] Check Corporation Role -> Removing jobs due to missing required role to process it.', [
            'fqcn' => get_class($job),
            'character_id' => $job->getToken()->character_id,
            'corporation_id' => $job->getCorporationId(),
            'required_roles' => $job->getRoles(),
        ]);

        $job->delete();
    }

    /**
     * Determine if the current character refresh token has
     * the roles needed to make the corporation API call.
     *
     * @param  \Seat\Eveapi\Jobs\AbstractAuthCorporationJob  $job
     * @return bool
     */
    public function isCorpCharacterWithRoles($job): bool
    {

        if (! property_exists($job, 'roles'))
            return false;

        if (is_null($job->getToken()))
            return false;

        // Check the role needed for this call. The minimum role would
        // be configured in the roles attribute, but we will add the
        // 'Director' role as directors automatically have all roles.
        $required_roles = array_merge($job->getRoles(), ['Director']);
        $character_roles = $this->getCharacterRoles($job->getToken());

        logger()->debug(
            sprintf('[Jobs][Middlewares][%s] Check Corporation Role -> Retrieving character roles.', $job->job->getJobId()),
            [
                'fqcn' => get_class($job),
                'character_id' => $job->getToken()->character_id,
                'character_roles' => $character_roles,
            ]);

        // Perform the check.
        if (in_array($job->getScope(), $job->getToken()->scopes) &&
            ! empty(array_intersect($required_roles, $character_roles))) {

            return true;
        }

        // Considering a corporation role was required with the scope,
        // fail the authentication check. If we don't fail here, simply
        // granting the SSO scope would pass the next truth test.
        return false;
    }

    /**
     * @param  \Seat\Eveapi\Models\RefreshToken  $refreshToken
     * @return array
     */
    private function getCharacterRoles(RefreshToken $refreshToken): array
    {
        return CharacterRole::where('character_id', $refreshToken->character_id)
            // https://eve-seat.slack.com/archives/C0H3VGH4H/p1515081536000720
            // > @ccp_snowden: most things will require `roles`, most things are
            // > not contextually aware enough to make hq/base decisions
            ->where('scope', 'roles')
            ->pluck('role')
            ->all();
    }
}
