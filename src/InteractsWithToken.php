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

namespace Seat\Eveapi;

use Seat\Eveapi\Models\RefreshToken;
use Seat\Services\Contracts\EsiClient;

trait InteractsWithToken
{
    abstract protected function getClient(): EsiClient;

    abstract protected function getToken(): ?RefreshToken;

    /**
     * @return void
     */
    protected function configureTokenForEsiClient(): void
    {
        $token = $this->getToken();
        if($token !== null) {
            $this->getClient()->setAuthentication($token);
        }
    }

    /**
     * Update the access_token last used in the job,
     * along with the expiry time.
     *
     * @return void
     */
    public function updateRefreshToken(): void
    {
        $client = $this->getClient();
        $token = $this->getToken();

        // If  it is an unauthenticated call, there is nothing to update
        if (is_null($token))
            return;

        if (! $client->isAuthenticated())
            return;

        $last_auth = $client->getAuthentication();

        // update the token
        if (! empty($last_auth->getRefreshToken()))
            $token->refresh_token = $last_auth->getRefreshToken();
        $token->token = $last_auth->getAccessToken() ?? '-';
        $token->expires_on = $last_auth->getExpiresOn();
        $token->save();
    }
}
