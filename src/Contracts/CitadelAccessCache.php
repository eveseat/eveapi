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

namespace Seat\Eveapi\Contracts;

interface CitadelAccessCache
{
    const BLOCK_DURATION_SECONDS = 60 * 60 * 24 * 7 * 4; // 4 weeks

    /**
     * Checks whether a character can access a citadel or if esi will return an error 403.
     *
     * @param  int  $character_id
     * @param  int  $citadel_id
     * @return bool
     */
    public static function canAccess(int $character_id, int $citadel_id): bool;

    /**
     * After having received an error 403, block a character from further accesses.
     *
     * @param  int  $character_id
     * @param  int  $citadel_id
     * @return mixed
     */
    public static function blockAccess(int $character_id, int $citadel_id);
}
