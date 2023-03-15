<?php

namespace Seat\Eveapi\Jobs\Universe\Structures;

class CacheCitadelAccessCache implements CitadelAccessCache
{
    private static function getCacheKey(int $character_id, int $citadel_id){
        return "citadel.$citadel_id.block.$character_id";
    }

    public static function canAccess(int $character_id, int $citadel_id): bool
    {
        return cache()->get(self::getCacheKey($character_id,$citadel_id), true);
    }

    public static function blockAccess(int $character_id, int $citadel_id)
    {
        cache()->set(self::getCacheKey($character_id, $citadel_id), false, now()->addSeconds(self::BLOCK_DURATION_SECONDS));
    }
}