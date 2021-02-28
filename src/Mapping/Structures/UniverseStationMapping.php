<?php

namespace Seat\Eveapi\Mapping\Structures;

use Seat\Eveapi\Mapping\DataMapping;

/**
 * Class UniverseStationMapping.
 * @package Seat\Eveapi\Mapping\Structures
 */
class UniverseStationMapping extends DataMapping
{
    /**
     * @var array
     */
    protected static $mapping = [
        'type_id'                    => 'type_id',
        'name'                       => 'name',
        'owner'                      => 'owner',
        'race_id'                    => 'race_id',
        'x'                          => 'position.x',
        'y'                          => 'position.y',
        'z'                          => 'position.z',
        'system_id'                  => 'system_id',
        'reprocessing_efficiency'    => 'reprocessing_efficiency',
        'reprocessing_stations_take' => 'reprocessing_stations_take',
        'max_dockable_ship_volume'   => 'max_dockable_ship_volume',
        'office_rental_cost'         => 'office_rental_cost',
    ];
}
