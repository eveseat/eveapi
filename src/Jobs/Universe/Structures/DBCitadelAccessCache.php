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
use Seat\Eveapi\Models\Universe\CitadelAccessCache as CitadelAccessCacheModel;

class DBCitadelAccessCache implements CitadelAccessCache
{
    /**
     * @inheritDoc
     */
    public static function canAccess(int $character_id, int $citadel_id): bool
    {
        $entry = CitadelAccessCacheModel::where('character_id', $character_id)
            ->where('citadel_id', $citadel_id)
            ->where('last_failed_access', '>=', now()->subSeconds(self::BLOCK_DURATION_SECONDS))
            ->first();

        if($entry === null) return true;

        return false;
    }

    /**
     * @inheritDoc
     */
    public static function blockAccess(int $character_id, int $citadel_id)
    {
        $entry = CitadelAccessCacheModel::where('character_id', $character_id)
            ->where('citadel_id', $citadel_id)
            ->first();

        if($entry === null) {
            $entry = new CitadelAccessCacheModel();
            $entry->character_id = $character_id;
            $entry->citadel_id = $citadel_id;
        }

        $entry->last_failed_access = now();
        $entry->save();
    }
}
