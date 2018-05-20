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

namespace Seat\Eveapi\Traits;

/**
 * Trait RateLimitsCalls.
 * @package Seat\Eveapi\Traits
 */
trait RateLimitsCalls
{
    /**
     * Checks if the current cache key is to be considered rate limited.
     *
     * @return bool
     * @throws \Exception
     */
    public function isRateLimited(): bool
    {

        if (cache()->get($this->getRateLimitKey()) < $this->getRateLimit())
            return false;

        return true;
    }

    /**
     * Get the rate limiting cache key to use.
     *
     * @return string
     */
    private function getRateLimitKey(): string
    {

        if (property_exists($this, 'rate_limit_key'))
            return $this->rate_limit_key;
        else
            return get_class($this) . '.rate_limit';
    }

    /**
     * Get the actual rate limit.
     *
     * @return int
     */
    private function getRateLimit(): int
    {

        if (property_exists($this, 'rate_limit'))
            return $this->rate_limit;
        else
            return 20;
    }

    /**
     * Increments the number of calls that have been made.
     *
     * A rate limit should only live for one minute. This is
     * because CCP reset the error count every minute.
     *
     * @param int $amount
     *
     * @throws \Psr\SimpleCache\InvalidArgumentException
     */
    public function incrementRateLimitCallCount(int $amount = 1)
    {

        cache()->set($this->getRateLimitKey(), $amount, carbon('now')->addMinute(1));
    }
}
