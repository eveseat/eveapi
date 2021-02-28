<?php

namespace Seat\Eveapi\Mapping\Assets;

use Seat\Eveapi\Mapping\DataMapping;

/**
 * Class AssetMapping.
 * @package Seat\Eveapi\Mapping\Assets
 */
class AssetMapping extends DataMapping
{
    /**
     * @var string[]
     */
    protected static $mapping = [
        'item_id' => 'item_id',
        'type_id' => 'type_id',
        'quantity' => 'quantity',
        'location_id' => 'location_id',
        'location_type' => 'location_type',
        'location_flag' => 'location_flag',
        'is_singleton' => 'is_singleton',
    ];
}
