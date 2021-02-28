<?php

namespace Seat\Eveapi\Mapping\Structures;

use Seat\Eveapi\Mapping\DataMapping;

/**
 * Class CorporationStructureMapping.
 * @package Seat\Eveapi\Mapping\Structures
 */
class CorporationStructureMapping extends DataMapping
{
    /**
     * @var array
     */
    protected static $mapping = [
        'structure_id'           => 'structure_id',
        'corporation_id'         => 'corporation_id',
        'type_id'                => 'type_id',
        'system_id'              => 'system_id',
        'profile_id'             => 'profile_id',
        'fuel_expires'           => 'fuel_expires',
        'state_timer_start'      => 'state_timer_start',
        'state_timer_end'        => 'state_timer_end',
        'unanchors_at'           => 'unanchors_at',
        'state'                  => 'state',
        'reinforce_weekday'      => 'reinforce_weekday',
        'reinforce_hour'         => 'reinforce_hour',
        'next_reinforce_weekday' => 'next_reinforce_weekday',
        'next_reinforce_hour'    => 'next_reinforce_hour',
        'next_reinforce_apply'   => 'next_reinforce_apply',
    ];
}
