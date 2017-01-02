<?php

/*
 * This file is part of SeAT
 *
 * Copyright (C) 2015, 2016, 2017  Leon Jacobs
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

namespace Seat\Eveapi\Api\Server;

use Seat\Eveapi\Api\Base;
use Seat\Eveapi\Models\Server\ServerStatus as StatusModel;

/**
 * Class ServerStatus.
 * @package Seat\Eveapi\Server
 */
class ServerStatus extends Base
{
    /**
     * Run the Update.
     */
    public function call()
    {

        $result = $this->setScope('server')
            ->getPheal()
            ->ServerStatus();

        if (
            StatusModel::orderBy('currentTime', 'desc')
                ->value('currentTime') != $result->request_time
        ) {

            StatusModel::create([
                'currentTime'   => $result->request_time,
                'serverOpen'    => $result->serverOpen,
                'onlinePlayers' => $result->onlinePlayers,
            ]);
        }

    }
}
