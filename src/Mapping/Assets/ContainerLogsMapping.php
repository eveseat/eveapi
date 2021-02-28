<?php

namespace Seat\Eveapi\Mapping\Assets;

use Seat\Eveapi\Mapping\DataMapping;

/**
 * Class ContainerLogsMapping.
 * @package Seat\Eveapi\Mapping\Assets
 */
class ContainerLogsMapping extends DataMapping
{
    /**
     * @var array
     */
    protected static $mapping = [
        'container_id'       => 'container_id',
        'logged_at'          => 'logged_at',
        'container_type_id'  => 'container_type_id',
        'character_id'       => 'character_id',
        'location_id'        => 'location_id',
        'action'             => 'action',
        'location_flag'      => 'location_flag',
        'password_type'      => 'password_type',
        'type_id'            => 'type_id',
        'quantity'           => 'quantity',
        'old_config_bitmask' => 'old_config_bitmask',
        'new_config_bitmask' => 'new_config_bitmask',
    ];
}
