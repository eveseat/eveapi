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

namespace Seat\Eveapi\Jobs\Universe\Structures;

use Seat\Eveapi\Contracts\CitadelAccessCache;

abstract class AbstractCitadelAccessCache implements CitadelAccessCache
{
    /**
     * Returns a randomized block duration on the order of self::BLOCK_DURATION_SECONDS.
     *
     * @return int
     */
    protected static function getRandomizedBlockDuration(): int
    {
        return rand((int) (self::BLOCK_DURATION_SECONDS * 0.5), (int) (self::BLOCK_DURATION_SECONDS * 1.5));
    }
}
