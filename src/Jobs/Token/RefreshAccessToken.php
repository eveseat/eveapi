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
use Seat\Eseye\Exceptions\InvalidContainerDataException;
use Seat\Eveapi\InteractsWithToken;
use Seat\Eveapi\Jobs\AbstractJob;
use Seat\Eveapi\Models\RefreshToken;
use Seat\Services\Contracts\EsiClient;

class RefreshAccessToken extends AbstractJob
{
    use InteractsWithToken;

    /**
     * @var array
     */
    protected $tags = ['character', 'token'];

    /**
     * @var \Seat\Eveapi\Models\RefreshToken
     */
    protected $token;

    /**
     * @var EsiClient
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
     *
     * @throws InvalidContainerDataException
     */
    public function handle(): void
    {
        // pass this token to the esi client
        $this->configureTokenForEsiClient();

        // ensure we have a valid access token
        $this->esi->getValidAccessToken();

        // make sure the new token is stored
        $this->updateRefreshToken();
    }

    public function getClient(): EsiClient
    {
        return $this->esi;
    }

    public function getToken(): ?RefreshToken
    {
        return $this->token;
    }
}
