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

use Illuminate\Support\Facades\Redis;

/**
 * Trait RateLimitsEsiCalls.
 * @package Seat\Eveapi\Traits
 */
trait RateLimitsEsiCalls
{

    /**
     * @var string
     */
    protected $ratelimit_key = 'esiratelimit';

    /**
     * @var int
     */
    protected $ratelimit = 80;

    /**
     * Number of minutes to consider the rate limit
     * for errors.
     *
     * @var int
     */
    protected $ratelimit_duration = 5;

    /**
     * @return bool
     * @throws \Exception
     */
    public function isEsiRateLimited(): bool
    {

        if (cache()->get($this->ratelimit_key) < $this->ratelimit)
            return false;

        return true;
    }

    /**
     * @param int $amount
     *
     * @throws \Psr\SimpleCache\InvalidArgumentException
     */
    public function incrementEsiRateLimit(int $amount = 1)
    {

        if ($this->getRateLimitKeyTtl() > 3) {

            cache()->increment($this->ratelimit_key, $amount);

        } else {

            cache()->set($this->ratelimit_key, $amount, carbon('now')
                ->addMinutes($this->ratelimit_duration));
        }
    }

    /**
     * @return mixed
     */
    public function getRateLimitKeyTtl()
    {

        return Redis::ttl('seat:' . $this->ratelimit_key);
    }
}
