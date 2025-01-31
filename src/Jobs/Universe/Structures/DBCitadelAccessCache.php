<?php

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
            ->where('last_failed_access','>=',now()->subSeconds(self::BLOCK_DURATION_SECONDS))
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