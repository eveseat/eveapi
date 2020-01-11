<?php

/*
 * This file is part of SeAT
 *
 * Copyright (C) 2015, 2016, 2017, 2018, 2019  Leon Jacobs
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
use Seat\Eveapi\Jobs\Middleware\IgnoreNpcCorporation;
use Seat\Eveapi\Jobs\Middleware\RequireCorporationRole;
use Seat\Eveapi\Models\RefreshToken;

/**
 * Class AbstractAuthenticatedCorporationJob.
 *
 * @package Seat\Eveapi\Jobs
 */
abstract class AbstractAuthCorporationJob extends AbstractCorporationJob
{
    /**
     * {@inheritdoc}
     */
    public $queue = 'corporations';

    /**
     * The roles which are required in order to get access to an endpoint; in addition of a scope.
     *
     * @var array
     */
    protected $roles = [];

    /**
     * AbstractCorporationJob constructor.
     *
     * @param int $corporation_id
     * @param \Seat\Eveapi\Models\RefreshToken $token
     */
    public function __construct(int $corporation_id, RefreshToken $token)
    {
        $this->token = $token;

        parent::__construct($corporation_id);
    }

    /**
     * @return array
     */
    public function middleware()
    {
        return array_merge(parent::middleware(), [
            new CheckTokenScope,
            new IgnoreNpcCorporation,
            new RequireCorporationRole,
        ]);
    }
}
