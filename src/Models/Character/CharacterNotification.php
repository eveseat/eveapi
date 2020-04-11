<?php

/*
 * This file is part of SeAT
 *
 * Copyright (C) 2015 to 2020 Leon Jacobs
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

namespace Seat\Eveapi\Models\Character;

use Illuminate\Database\Eloquent\Model;
use Seat\Eveapi\Models\Universe\UniverseName;

/**
 * Class CharacterNotification.
 * @package Seat\Eveapi\Models\Character
 *
 * @SWG\Definition(
 *     description="Character Notification",
 *     title="CharacterNotification",
 *     type="object"
 * )
 *
 * @SWG\Property(
 *     type="integer",
 *     property="notification_id",
 *     description="The notification identifier"
 * )
 *
 * @SWG\Property(
 *     type="string",
 *     property="type",
 *     description="The notification type"
 * )
 *
 * @SWG\Property(
 *     type="integer",
 *     format="int64",
 *     property="sender_id",
 *     description="The entity who sent the notification"
 * )
 *
 * @SWG\Property(
 *     type="string",
 *     enum={"character","corporation","alliance","faction","other"},
 *     property="sender_type",
 *     description="The sender qualifier"
 * )
 *
 * @SWG\Property(
 *     type="string",
 *     format="date-time",
 *     property="timestamp",
 *     description="The date-time when notification has been sent"
 * )
 *
 * @SWG\Property(
 *     type="boolean",
 *     property="is_read",
 *     description="True if the notification has been red"
 * )
 *
 * @SWG\Property(
 *     type="string",
 *     property="text",
 *     description="The notification content"
 * )
 *
 * @SWG\Property(
 *     type="string",
 *     format="date-time",
 *     property="created_at",
 *     description="The date-time when notification has been created into SeAT"
 * )
 *
 * @SWG\Property(
 *     type="string",
 *     format="date-time",
 *     property="updated_at",
 *     description="The date-time when notification has been updated into SeAT"
 * )
 */
class CharacterNotification extends Model
{
    /**
     * @var bool
     */
    protected static $unguarded = true;

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasOne
     */
    public function sender()
    {
        return $this->hasOne(UniverseName::class, 'entity_id', 'sender_id');
    }
}
