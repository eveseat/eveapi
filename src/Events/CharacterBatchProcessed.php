<?php

namespace Seat\Eveapi\Events;

use Illuminate\Queue\SerializesModels;
use Seat\Eveapi\Models\Character\CharacterInfo;
use Seat\Eveapi\Models\RefreshToken;

class CharacterBatchProcessed
{
    use SerializesModels;

    public CharacterInfo $character;

    /**
     * @param CharacterInfo $character
     */
    public function __construct(CharacterInfo $character)
    {
        $this->character = $character;
    }
}