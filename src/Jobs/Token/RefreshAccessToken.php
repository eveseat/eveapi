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

namespace Seat\Eveapi\Jobs\Token;

use Illuminate\Contracts\Container\BindingResolutionException;
use Seat\Eseye\Exceptions\RequestFailedException;
use Seat\Eveapi\Jobs\AbstractJob;
use Seat\Eveapi\Models\RefreshToken;
use Seat\Services\Contracts\EsiClient;

class RefreshAccessToken extends AbstractJob
{
    /**
     * @var array
     */
    protected $tags = ['character', 'token'];

    /**
     * @var \Seat\Eveapi\Models\RefreshToken
     */
    protected $token;

    /**
     * @var \Seat\Services\Contracts\EsiClient
     */
    protected EsiClient $esi;

    /**
     * @param  RefreshToken  $token
     *
     * @throws BindingResolutionException
     */
    public function __construct(RefreshToken $token)
    {
        $this->token = $token;
        $this->esi = app()->make(EsiClient::class);
    }

    /**
     * @return void
     */
    public function handle()
    {
        // normally the retrieve function passes the token down the esi stack, but we don't use retrieve
        $this->esi->setAuthentication($this->token);

        try {
            // get or renew access token
            $this->esi->getValidAccessToken();
        } catch (RequestFailedException $e) {

        }

        // save the new access token. the following logic is extracted from EsiBase
        $this->token = $this->token->fresh(); // since the model might have been in the queue for a while, amke sure to get the latest info
        $last_auth = $this->esi->getAuthentication(); // extract the access token info from eseye

        if (! empty($last_auth->getRefreshToken()))
            $this->token->refresh_token = $last_auth->getRefreshToken();

        $this->token->token = $last_auth->getAccessToken() ?? '-';
        $this->token->expires_on = $last_auth->getExpiresOn();

        $this->token->save();
    }
}
