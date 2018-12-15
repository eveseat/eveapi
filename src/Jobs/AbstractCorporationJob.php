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
        $unique_key = implode(':', array_merge($this->tags, [$this->getCorporationId()]));

        Redis::throttle($unique_key)->allow(1)->every(600)->then(function () use ($unique_key) {
            logger()->debug(sprintf('%s has been queued | tags: %s | owner: %s', get_class($this), $unique_key, $this->getCharacterId()));

            if (! $this->preflighted()) return;

            $this->job();
        }, function () use ($unique_key) {
            logger()->debug(sprintf('%s has been dropped (throttler) | tags: %s | owner: %s', get_class($this), $unique_key, $this->getCharacterId()));

            return $this->delete();
        });
    }

    /**
     * Contains the job process.
     *
     * @return void
     * @throws \Throwable
     */
    abstract protected function job(): void;
}
