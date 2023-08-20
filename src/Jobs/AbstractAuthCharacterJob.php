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

namespace Seat\Eveapi\Jobs;

use Illuminate\Queue\Middleware\WithoutOverlapping;
use Seat\Eveapi\Jobs\Middleware\CheckTokenScope;
use Seat\Eveapi\Jobs\Middleware\CheckTokenVersion;
use Seat\Eveapi\Models\RefreshToken;

/**
 * Class AbstractAuthenticatedCharacterJob.
 *
 * @package Seat\Eveapi\Jobs
 */
abstract class AbstractAuthCharacterJob extends AbstractCharacterJob
{
    /**
     * {@inheritdoc}
     */
    public $queue = 'characters';

    /**
     * AbstractCharacterJob constructor.
     *
     * @param  \Seat\Eveapi\Models\RefreshToken  $token
     */
    public function __construct(RefreshToken $token)
    {
        $this->token = $token;

        parent::__construct($token->character_id);
    }

    /**
     * @return array
     */
    public function middleware()
    {
        return array_merge(parent::middleware(), [
            new CheckTokenScope,
            new CheckTokenVersion,
            (new WithoutOverlapping($this->getToken()->character_id))
                ->releaseAfter(self::ANTI_RACE_DELAY)
                ->expireAfter(self::ACCESS_TOKEN_EXPIRY_DELAY),
        ]);
    }
}
