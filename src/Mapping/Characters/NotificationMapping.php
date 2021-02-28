<?php

namespace Seat\Eveapi\Mapping\Characters;

use Seat\Eveapi\Mapping\DataMapping;

/**
 * Class NotificationMapping.
 * @package Seat\Eveapi\Mapping\Characters
 */
class NotificationMapping extends DataMapping
{
    /**
     * @var array
     */
    protected static $mapping = [
        'notification_id' => 'notification_id',
        'type'            => 'type',
        'sender_id'       => 'sender_id',
        'sender_type'     => 'sender_type',
        'timestamp'       => 'timestamp',
        'is_read'         => 'is_read',
        'text'            => 'text',
    ];
}
