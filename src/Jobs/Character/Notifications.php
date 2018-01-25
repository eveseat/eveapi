<?php

/*
 * This file is part of SeAT
 *
 * Copyright (C) 2015, 2016, 2017, 2018  Leon Jacobs
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

namespace Seat\Eveapi\Jobs\Character;


use Seat\Eveapi\Jobs\EsiBase;
use Seat\Eveapi\Models\Character\CharacterNotification;

/**
 * Class Notifications
 * @package Seat\Eveapi\Jobs\Character
 */
class Notifications extends EsiBase
{
    /**
     * @var string
     */
    protected $method = 'get';

    /**
     * @var string
     */
    protected $endpoint = '/characters/{character_id}/notifications/';

    /**
     * @var int
     */
    protected $version = 'v1';

    /**
     * @var string
     */
    protected $scope = 'esi-characters.read_notifications.v1';

    /**
     * @var array
     */
    protected $tags = ['character', 'notifications'];

    /**
     * Execute the job.
     *
     * @return void
     * @throws \Exception
     * @throws \Throwable
     */
    public function handle()
    {

        $notifications = $this->retrieve([
            'character_id' => $this->getCharacterId(),
        ]);

        collect($notifications)->each(function ($notification) {

            CharacterNotification::firstOrCreate([
                'character_id'    => $this->getCharacterId(),
                'notification_id' => $notification->notification_id,
                'type'            => $notification->type,
                'sender_id'       => $notification->sender_id,
                'sender_type'     => $notification->sender_type,
                'timestamp'       => carbon($notification->timestamp),
                'is_read'         => isset($notification->is_read) ? $notification->is_read : false,
                'text'            => $notification->text,
            ]);
        });
    }
}
