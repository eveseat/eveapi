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

namespace Seat\Eveapi\Bus;

use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Support\Collection;
use Seat\Eveapi\Jobs\EsiBase;
use Seat\Eveapi\Models\RefreshToken;

/**
 * Interface Bus.
 *
 * @package Seat\Eveapi\Bus
 */
abstract class Bus
{
    /**
     * @var \Illuminate\Support\Collection
     */
    protected Collection $jobs;

    /**
     * @var \Seat\Eveapi\Models\RefreshToken|null
     */
    protected ?RefreshToken $token;

    /**
     * Bus constructor.
     */
    public function __construct(?RefreshToken $token)
    {
        $this->jobs = collect();
        $this->token = $token;
    }

    /**
     * Dispatch jobs.
     *
     * @return void
     */
    abstract public function fire(): void;

    /**
     * Checks if the scopes of the token allow this job and if so, add it to the job list.
     *
     * @param  EsiBase  $job
     * @return void
     */
    protected function addAuthenticatedJob(EsiBase $job): void
    {
        if(is_null($this->token)) return;

        if(in_array($job->getScope(), $this->token->getScopes())) {
            $this->jobs->add($job);
        }
    }

    /**
     * Add a public job to the job list.
     *
     * @param  ShouldQueue  $job
     * @return void
     */
    protected function addPublicJob(ShouldQueue $job): void
    {
        $this->jobs->add($job);
    }
}
