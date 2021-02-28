<?php

namespace Seat\Eveapi\Mapping\Characters;

use Seat\Eveapi\Mapping\DataMapping;

/**
 * Class CalendarDetailMapping.
 * @package Seat\Eveapi\Mapping\Characters
 */
class CalendarDetailMapping extends DataMapping
{
    /**
     * @var array
     */
    protected static $mapping = [
        'owner_id' => 'owner_id',
        'owner_name' => 'owner_name',
        'duration' => 'duration',
        'text' => 'text',
        'owner_type' => 'owner_type',
    ];
}
