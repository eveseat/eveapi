<?php

/*
 * This file is part of SeAT
 *
 * Copyright (C) 2015 to 2022 Leon Jacobs
 *
 * This program is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 2 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License along
 * with this program; if not, write to the Free Software Foundation, Inc.,
 * 51 Franklin Street, Fifth Floor, Boston, MA 02110-1301 USA.
 */

namespace Seat\Eveapi\Mapping\Characters;

use Seat\Eveapi\Mapping\DataMapping;

/**
 * Class NotificationMapping.
 *
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
