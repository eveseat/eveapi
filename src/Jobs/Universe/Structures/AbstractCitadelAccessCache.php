<?php

namespace Seat\Eveapi\Jobs\Universe\Structures;

use Seat\Eveapi\Contracts\CitadelAccessCache;

abstract class AbstractCitadelAccessCache implements CitadelAccessCache
{
    /**
     * Returns a randomized block duration on the order of self::BLOCK_DURATION_SECONDS
     * @return int
     */
    protected static function getRandomizedBlockDuration(): int
    {
        return rand((int)(self::BLOCK_DURATION_SECONDS*0.5),(int)(self::BLOCK_DURATION_SECONDS*1.5));
    }
}