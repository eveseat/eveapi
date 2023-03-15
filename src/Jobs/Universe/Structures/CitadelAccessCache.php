<?php

namespace Seat\Eveapi\Jobs\Universe\Structures;

interface CitadelAccessCache
{
    const BLOCK_DURATION_SECONDS = 60*60*24*7; // 1 week

    public static function canAccess(int $character_id, int $citadel_id): bool;
    public static function blockAccess(int $character_id, int $citadel_id);
}