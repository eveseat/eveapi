<?php

namespace Seat\Eveapi\Mapping\Structures;

use Seat\Eveapi\Mapping\DataMapping;

/**
 * Class StarbaseMapping.
 * @package Seat\Eveapi\Mapping\Structures
 */
class StarbaseMapping extends DataMapping
{
    /**
     * @var string[]
     */
    protected static $mapping = [
        'starbase_id'      => 'starbase_id',
        'moon_id'          => 'moon_id',
        'onlined_since'    => 'onlined_since',
        'reinforced_until' => 'reinforced_until',
        'state'            => 'state',
        'type_id'          => 'type_id',
        'system_id'        => 'system_id',
        'unanchor_at'      => 'unanchor_at',
    ];
}
