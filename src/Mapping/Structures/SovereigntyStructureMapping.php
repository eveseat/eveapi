<?php

namespace Seat\Eveapi\Mapping\Structures;

use Seat\Eveapi\Mapping\DataMapping;

/**
 * Class SovereigntyStructureMapping.
 * @package Seat\Eveapi\Mapping\Structures
 */
class SovereigntyStructureMapping extends DataMapping
{
    /**
     * @var array
     */
    protected static $mapping = [
        'structure_type_id'             => 'structure_type_id',
        'alliance_id'                   => 'alliance_id',
        'solar_system_id'               => 'solar_system_id',
        'vulnerability_occupancy_level' => 'vulnerability_occupancy_level',
        'vulnerable_start_time'         => 'vulnerable_start_time',
        'vulnerable_end_time'           => 'vulnerable_end_time',
    ];
}
