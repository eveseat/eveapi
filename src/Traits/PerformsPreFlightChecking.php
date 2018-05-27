<?php

/*
 * This file is part of SeAT
 *
 * Copyright (C) 2015, 2016, 2017, 2018  Leon Jacobs
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

namespace Seat\Eveapi\Traits;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Seat\Eveapi\Models\Status\EsiStatus;

/**
 * Trait PerformsPreFlightChecking.
 * @package Seat\Eveapi\Traits
 */
trait PerformsPreFlightChecking
{
    /**
     * Perform a number of preflight checks for an API call.
     *
     * These checks attempt to pre-empt failure conditions and
     * other cases where the API call might fail. Checks include
     * those where a token might be invalid, or insufficient
     * roles exists on a token to make a corporation call.
     *
     * Finally, if ESI is determined to be down, this will also
     * fail preflight checks.
     *
     * @return bool
     * @throws \Exception
     */
    public function preflighted(): bool
    {

        // Just stop if ESI is considered down.
        if ($this->isEsiDown()) return false;

        // Public endpoints need no checking
        if ($this->isPublicEndpoint()) return true;

        // Ignore NPC corporations by marking the job as unauthenticated.
        // This is admittedly a little hacky, so a better way is needed
        // more long term.
        if (in_array('corporation', $this->tags()) && $this->isNPCCorporation())
            return false;

        // Check if the current scope also needs a corp role. If it does,
        // ensure that the current character also has the required role
        // and the corporation is not an NPC corporation.
        if (count($this->roles) > 0) {

            // Don't process NPC corporations.
            if ($this->isNPCCorporation()) return false;

            // Ensure that the chracter has the needed roles to make
            // a call to this endpoint.
            if ($this->isCorpCharacterWithRoles())
                return true;
            else
                return false;
        }

        // If a corporation role is *not* required, check that we have the required
        // scope at least.
        if (in_array($this->scope, $this->token->scopes))
            return true;

        // Log the deny
        Log::debug('Denied call to ' . $this->endpoint . ' as scope ' . $this->scope . ' was missing.');

        return false;
    }

    /**
     * Determine if ESI could be down.
     *
     * The results of this call depends *heavily* on the
     * fact that the EsiPing job runs and gets output.
     *
     * @return bool
     */
    public function isEsiDown(): bool
    {

        // Check if we may have hit an error threshold
        if ($this->isEsiRateLimited()) return true;

        // Get the latest ESI status.
        $status = Cache::remember('esi_db_status', 1, function () {

            return EsiStatus::latest()->first();
        });

        // If we don't have a status yet, assume everything is ok.
        if (! $status) return false;

        // If the data is too old, return false by default.
        // Not being able to ping ESI could be indicative
        // of many other problems.
        if ($status->created_at->lte(carbon('now')->subHours(2)))
            return true;

        // If the status is OK, yay.
        if ($status->status == 'ok')
            return false;

        return true;
    }

    /**
     * Check for a public enpoint call.
     *
     * @return bool
     */
    public function isPublicEndpoint(): bool
    {

        // Public calls need no checking.
        if ($this->public_call || is_null($this->token) || $this->scope === 'public')
            return true;

        return false;
    }

    /**
     * Determine if the current corporation ID is in NPC corporation range.
     *
     * @return bool
     * @throws Exception
     */
    public function isNPCCorporation(): bool
    {

        // ID range references:
        //  https://gist.github.com/a-tal/5ff5199fdbeb745b77cb633b7f4400bb
        return 1000000 <= $this->getCorporationId() && $this->getCorporationId() <= 2000000;
    }

    /**
     * Determine if the current character refresh token has
     * the roles needed to make the corporation API call.
     *
     * @return bool
     */
    public function isCorpCharacterWithRoles(): bool
    {

        // Check the role needed for this call. The minimum role would
        // be configured in the roles attribute, but we will add the
        // 'Director' role as directors automatically have all roles.
        array_push($this->roles, 'Director');

        // Perform the check.
        if (in_array($this->scope, $this->token->scopes) && ! empty(
            array_intersect($this->roles, $this->getCharacterRoles()))) {

            return true;
        }

        // Considering a corporation role was required with the scope,
        // fail the authentication check. If we don't fail here, simply
        // granting the SSO scope would pass the next truth test.
        return false;
    }
}
