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

namespace Seat\Eveapi\Bus;

use Seat\Eveapi\Jobs\Alliances\Info;
use Seat\Eveapi\Jobs\Alliances\Members;
use Seat\Eveapi\Jobs\Contacts\Alliance\Contacts;
use Seat\Eveapi\Jobs\Contacts\Alliance\Labels;
use Seat\Eveapi\Models\RefreshToken;

/**
 * Class Alliance.
 *
 * @package Seat\Eveapi\Bus
 */
class Alliance extends Bus
{
    /**
     * @var int
     */
    private $alliance_id;

    /**
     * @var \Seat\Eveapi\Models\RefreshToken
     */
    private $token;

    /**
     * Alliance constructor.
     *
     * @param int $alliance_id
     * @param \Seat\Eveapi\Models\RefreshToken|null $token
     */
    public function __construct(int $alliance_id, ?RefreshToken $token = null)
    {
        parent::__construct();

        $this->token = $token;
        $this->alliance_id = $alliance_id;
    }

    /**
     * Dispatch jobs.
     *
     * @return void
     */
    public function fire()
    {
        $this->addPublicJobs();

        if (! is_null($this->token))
            $this->addAuthenticatedJobs();

        Info::withChain($this->jobs->toArray())
            ->dispatch($this->alliance_id)
            ->delay(now()->addSeconds(rand(120, 600)));
        // in order to prevent ESI to receive massive income of all existing SeAT instances in the world
        // add a bit of randomize when job can be processed - we use seconds here, so we have more flexibility
        // https://github.com/eveseat/seat/issues/731
    }

    /**
     * Seed jobs list with job which did not require authentication.
     *
     * @return void
     */
    protected function addPublicJobs()
    {
        $this->jobs->add(new Members($this->alliance_id));
    }

    /**
     * Seed jobs list with job requiring authentication.
     *
     * @return void
     */
    protected function addAuthenticatedJobs()
    {
        $this->jobs->add(new Labels($this->alliance_id, $this->token));
        $this->jobs->add(new Contacts($this->alliance_id, $this->token));
    }
}
