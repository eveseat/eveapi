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

 /*
  * This file is sourced from Laravel 8.x while SeAT is using Laravel 6.x
  * Once SeAT is updated to a later version of Laravel this file should be removed.
  */

namespace Seat\Eveapi\Jobs\Middleware;

use Illuminate\Container\Container;
use Illuminate\Contracts\Cache\Repository as Cache;
use Illuminate\Support\InteractsWithTime;

class WithoutOverlapping
{
    use InteractsWithTime;

    // ANTI_RACE_DELAY prevents rapid job recylcing with low queue depths
    const ANTI_RACE_DELAY = 10;

    // ACCESS_TOKEN_EXPIRY_DELAY forces lock release after 25m (as access token exipry is 20m)
    const ACCESS_TOKEN_EXPIRY_DELAY = 25 * 60;


    /**
     * The job's unique key used for preventing overlaps.
     *
     * @var string
     */
    public $key;

    /**
     * The number of seconds before a job should be available again if no lock was acquired.
     *
     * @var \DateTimeInterface|int|null
     */
    public $releaseAfter;

    /**
     * The number of seconds before the lock should expire.
     *
     * @var int
     */
    public $expiresAfter;

    /**
     * The prefix of the lock key.
     *
     * @var string
     */
    public $prefix = 'laravel-queue-overlap:';

    /**
     * Create a new middleware instance.
     *
     * @param  string  $key
     * @param  \DateTimeInterface|int|null  $releaseAfter
     * @param  \DateTimeInterface|int  $expiresAfter
     * @return void
     */
    public function __construct($key = '', $releaseAfter = 0, $expiresAfter = 0)
    {
        $this->key = $key;
        $this->releaseAfter = $releaseAfter;
        $this->expiresAfter = $this->secondsUntil($expiresAfter);
    }

    /**
     * Process the job.
     *
     * @param  mixed  $job
     * @param  callable  $next
     * @return mixed
     */
    public function handle($job, $next)
    {
        $lock = Container::getInstance()->make(Cache::class)->lock(
            $this->getLockKey($job), $this->expiresAfter
        );

        if ($lock->get()) {
            try {
                $next($job);
            } finally {
                $lock->release();
            }
        } elseif (! is_null($this->releaseAfter)) {
            $job->release($this->releaseAfter);
        }
    }

    /**
     * Set the delay (in seconds) to release the job back to the queue.
     *
     * @param  int  $releaseAfter
     * @return $this
     */
    public function releaseAfter($releaseAfter)
    {
        $this->releaseAfter = $releaseAfter;

        return $this;
    }

    /**
     * Do not release the job back to the queue if no lock can be acquired.
     *
     * @return $this
     */
    public function dontRelease()
    {
        $this->releaseAfter = null;

        return $this;
    }

    /**
     * Set the maximum number of seconds that can elapse before the lock is released.
     *
     * @param  \DateTimeInterface|int  $expiresAfter
     * @return $this
     */
    public function expireAfter($expiresAfter)
    {
        $this->expiresAfter = $this->secondsUntil($expiresAfter);

        return $this;
    }

    /**
     * Set the prefix of the lock key.
     *
     * @param  string  $prefix
     * @return $this
     */
    public function withPrefix(string $prefix)
    {
        $this->prefix = $prefix;

        return $this;
    }

    /**
     * Get the lock key for the given job.
     *
     * @param  mixed  $job
     * @return string
     */
    public function getLockKey($job)
    {
        return $this->prefix . ':' . $this->key;
    }
}
