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

namespace Seat\Eveapi\Jobs;

use Seat\Eveapi\Jobs\Middleware\CheckTokenScope;
use Seat\Eveapi\Jobs\Middleware\CheckTokenVersion;
use Seat\Eveapi\Jobs\Middleware\IgnoreNpcCorporation;
use Seat\Eveapi\Jobs\Middleware\RequireCorporationRole;
use Seat\Eveapi\Jobs\Middleware\WithoutOverlapping;
use Seat\Eveapi\Models\RefreshToken;

/**
 * Class AbstractAuthenticatedAllianceJob.
 *
 * @package Seat\Eveapi\Jobs
 */
abstract class AbstractAuthAllianceJob extends AbstractAllianceJob
{
    /**
     * {@inheritdoc}
     */
    public $queue = 'default';

    /**
     * The roles which are required in order to get access to an endpoint; in addition of a scope.
     *
     * @var array
     */
    protected $roles = [];

    /**
     * AbstractAllianceJob constructor.
     *
     * @param  int  $alliance_id
     * @param  \Seat\Eveapi\Models\RefreshToken  $token
     */
    public function __construct(int $alliance_id, RefreshToken $token)
    {
        $this->token = $token;

        parent::__construct($alliance_id);
    }

    /**
     * @return array
     */
    public function middleware()
    {
        return array_merge(parent::middleware(), [
            new CheckTokenScope,
            new CheckTokenVersion,
            new IgnoreNpcCorporation,
            new RequireCorporationRole,
            (new WithoutOverlapping($this->getToken()->character_id))
                ->releaseAfter(WithoutOverlapping::ANTI_RACE_DELAY)
                ->expireAfter(WithoutOverlapping::ACCESS_TOKEN_EXPIRY_DELAY),
        ]);
    }
}
