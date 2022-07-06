<?php

namespace Seat\Eveapi\Models\Character;

use Illuminate\Database\Eloquent\Relations\Pivot;
use Seat\Eveapi\Models\Corporation\CorporationInfo;

class CharacterLoyaltyPoints extends Pivot
{
    protected $table = 'character_loyalty_points';

    public function character()
    {
        return $this->belongsTo(CharacterInfo::class, 'character_id', 'character_id');
    }

    public function corporation()
    {
        return $this->belongsTo(CorporationInfo::class, 'corporation_id', 'corporation_id');
    }
}