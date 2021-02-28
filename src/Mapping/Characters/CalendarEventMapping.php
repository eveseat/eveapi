<?php

namespace Seat\Eveapi\Mapping\Characters;

use Seat\Eveapi\Mapping\DataMapping;

/**
 * Class CalendarEventMapping.
 * @package Seat\Eveapi\Mapping\Characters
 */
class CalendarEventMapping extends DataMapping
{
    /**
     * @var string[]
     */
    protected static $mapping = [
        'event_id'       => 'event_id',
        'event_date'     => 'event_date',
        'title'          => 'title',
        'importance'     => 'importance',
        'event_response' => 'event_response',
    ];
}
