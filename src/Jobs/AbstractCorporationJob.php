<?php

/*
 * This file is part of SeAT
 *
 * Copyright (C) 2015 to 2020 Leon Jacobs
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

/**
 * Class AbstractCorporationJob.
 * @package Seat\Eveapi\Jobs
 */
abstract class AbstractCorporationJob extends EsiBase
{
    /**
     * @var array
     */
    protected $tags = ['corporation'];

    /**
     * Execute the job.
     *
     * @return void
     * @throws \Throwable
     */
    public function handle()
    {
        if (! $this->preflighted()) return;

        $this->job();
    }

    /**
     * @return string
     * @throws \Exception
     */
    private function getUniqueKey(): string
    {
        return implode(':', array_merge($this->tags, [$this->getCorporationId()]));
    }

    /**
     * Contains the job process.
     *
     * @return void
     * @throws \Throwable
     */
    abstract protected function job(): void;
}
