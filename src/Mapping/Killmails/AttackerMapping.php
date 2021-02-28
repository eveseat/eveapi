<?php

namespace Seat\Eveapi\Mapping\Killmails;

use Seat\Eveapi\Mapping\DataMapping;

/**
 * Class AttackerMapping.
 * @package Seat\Eveapi\Mapping\Killmails
 */
class AttackerMapping extends DataMapping
{
    /**
     * @var array
     */
    protected static $mapping = [
        'character_id'    => 'character_id',
        'corporation_id'  => 'corporation_id',
        'alliance_id'     => 'alliance_id',
        'faction_id'      => 'faction_id',
        'security_status' => 'security_status',
        'final_blow'      => 'final_blow',
        'damage_done'     => 'damage_done',
        'ship_type_id'    => 'ship_type_id',
        'weapon_type_id'  => 'weapon_type_id',
    ];
}
