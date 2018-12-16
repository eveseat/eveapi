<?php

/*
 * This file is part of SeAT
 *
 * Copyright (C) 2015, 2016, 2017, 2018  Leon Jacobs
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

use Illuminate\Support\Facades\Redis;

/**
 * Class AbstractCorporationJob.
 * @package Seat\Eveapi\Jobs
 */
abstract class AbstractCorporationJob extends EsiBase
{
    /**
     * @var int
     */
    protected $max_concurrent_jobs = 1;

    /**
     * @var array
     */
    protected $tags = ['corporation'];

    /**
     * @var int
     */
    protected $throttle_seconds = 600;

    /**
     * Execute the job.
     *
     * @return void
     * @throws \Throwable
     */
    public function handle()
    {
        Redis::throttle($this->getUniqueKey())->allow($this->max_concurrent_jobs)->every($this->throttle_seconds)->then(function () {
            logger()->debug(sprintf('%s has been queued | tags: %s | owner: %s',
                get_class($this), $this->getUniqueKey(), $this->getCharacterId()));

            if (! $this->preflighted()) return;

            $this->job();
        }, function () {
            logger()->debug(sprintf('%s has been dropped (throttler) | tags: %s | owner: %s',
                get_class($this), $this->getUniqueKey(), $this->getCharacterId()));

            return $this->delete();
        });
    }

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
