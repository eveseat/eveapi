<?php

namespace Seat\Eveapi\Mapping\Industry;

use Seat\Eveapi\Mapping\DataMapping;

/**
 * Class BlueprintMapping.
 * @package Seat\Eveapi\Mapping\Industry
 */
class BlueprintMapping extends DataMapping
{
    /**
     * @var array
     */
    protected static $mapping = [
        'item_id'             => 'item_id',
        'type_id'             => 'type_id',
        'location_flag'       => 'location_flag',
        'location_id'         => 'location_id',
        'quantity'            => 'quantity',
        'time_efficiency'     => 'time_efficiency',
        'material_efficiency' => 'material_efficiency',
        'runs'                => 'runs',
    ];
}
