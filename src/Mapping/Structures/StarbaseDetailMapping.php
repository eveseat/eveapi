<?php

namespace Seat\Eveapi\Mapping\Structures;

use Seat\Eveapi\Mapping\DataMapping;

/**
 * Class StarbaseDetailMapping.
 * @package Seat\Eveapi\Mapping\Structures
 */
class StarbaseDetailMapping extends DataMapping
{
    /**
     * @var array
     */
    protected static $mapping = [
        'fuel_bay_view'                            => 'fuel_bay_view',
        'fuel_bay_take'                            => 'fuel_bay_take',
        'anchor'                                   => 'anchor',
        'unanchor'                                 => 'unanchor',
        'online'                                   => 'online',
        'offline'                                  => 'offline',
        'allow_corporation_members'                => 'allow_corporation_members',
        'allow_alliance_members'                   => 'allow_alliance_members',
        'use_alliance_standings'                   => 'use_alliance_standings',
        'attack_standing_threshold'                => 'attack_standing_threshold',
        'attack_security_status_threshold'         => 'attack_security_status_threshold',
        'attack_if_other_security_status_dropping' => 'attack_if_other_security_status_dropping',
        'attack_if_at_war'                         => 'attack_if_at_war',
    ];
}
