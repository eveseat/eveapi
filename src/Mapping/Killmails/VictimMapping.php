<?php

namespace Seat\Eveapi\Mapping\Killmails;

use Seat\Eveapi\Mapping\DataMapping;

/**
 * Class VictimMapping.
 * @package Seat\Eveapi\Mapping\Killmails
 */
class VictimMapping extends DataMapping
{
    /**
     * @var array
     */
    protected static $mapping = [
        'character_id'   => 'character_id',
        'corporation_id' => 'corporation_id',
        'alliance_id'    => 'alliance_id',
        'faction_id'     => 'faction_id',
        'damage_taken'   => 'damage_taken',
        'ship_type_id'   => 'ship_type_id',
        'x'              => 'position.x',
        'y'              => 'position.y',
        'z'              => 'position.z',
    ];
}
