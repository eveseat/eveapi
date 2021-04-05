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
    protected $jobs;

    /**
     * Bus constructor.
     */
    public function __construct()
    {
        $this->jobs = collect();
    }

    /**
     * Dispatch jobs.
     *
     * @return void
     */
    abstract public function fire();

    /**
     * Seed jobs list with job which did not require authentication.
     *
     * @return void
     */
    abstract protected function addPublicJobs();

    /**
     * Seed jobs list with job requiring authentication.
     *
     * @return void
     */
    abstract protected function addAuthenticatedJobs();
}
