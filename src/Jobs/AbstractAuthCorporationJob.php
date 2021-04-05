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

use Exception;
use Seat\Eseye\Exceptions\RequestFailedException;
use Seat\Eveapi\Jobs\Middleware\CheckTokenScope;
use Seat\Eveapi\Jobs\Middleware\CheckTokenVersion;
use Seat\Eveapi\Jobs\Middleware\IgnoreNpcCorporation;
use Seat\Eveapi\Jobs\Middleware\RequireCorporationRole;
use Seat\Eveapi\Models\Character\CharacterAffiliation;
use Seat\Eveapi\Models\Corporation\CorporationMemberTracking;
use Seat\Eveapi\Models\RefreshToken;

/**
 * Class AbstractAuthenticatedCorporationJob.
 *
 * @package Seat\Eveapi\Jobs
 */
abstract class AbstractAuthCorporationJob extends AbstractCorporationJob
{
    const CHARACTER_NOT_IN_CORPORATION = 'Character is not in the corporation';

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
            new CheckTokenVersion,
            new IgnoreNpcCorporation,
            new RequireCorporationRole,
        ]);
    }

    /**
     * @param \Exception $exception
     *
     * @throws \Exception
     */
    public function failed(Exception $exception)
    {
        if (is_a($exception, RequestFailedException::class)) {
            if ($exception->getError() == self::CHARACTER_NOT_IN_CORPORATION) {

                // remove character from the corporation, if it's not updated yet
                CorporationMemberTracking::where('character_id', $this->token->character_id)
                    ->where('corporation_id', $this->corporation_id)
                    ->delete();

                // force remove character <-> corporation relation
                CharacterAffiliation::where('character_id', $this->token->character_id)
                    ->where('corporation_id', $this->corporation_id)
                    ->delete();

                return;
            }
        }

        parent::failed($exception);
    }
}
