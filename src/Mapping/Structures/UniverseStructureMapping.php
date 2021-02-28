<?php

namespace Seat\Eveapi\Mapping\Structures;

use Seat\Eveapi\Mapping\DataMapping;

/**
 * Class UniverseStructureMapping.
 * @package Seat\Eveapi\Mapping\Structures
 */
class UniverseStructureMapping extends DataMapping
{
    /**
     * @var array
     */
    protected static $mapping = [
        'name'            => 'name',
        'owner_id'        => 'owner_id',
        'solar_system_id' => 'solar_system_id',
        'x'               => 'position.x',
        'y'               => 'position.y',
        'z'               => 'position.z',
        'type_id'         => 'type_id',
    ];
}
