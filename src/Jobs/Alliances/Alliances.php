<?php

/*
 * This file is part of SeAT
 *
 * Copyright (C) 2015, 2016, 2017, 2018, 2019  Leon Jacobs
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

namespace Seat\Eveapi\Jobs\Alliances;

use Seat\Eveapi\Jobs\EsiBase;

/**
 * Class Alliances.
 * @package Seat\Eveapi\Jobs\Alliances
 */
class Alliances extends EsiBase
{
    /**
     * @var string
     */
    protected $method = 'get';

    /**
     * @var string
     */
    protected $endpoint = '/alliances/';

    /**
     * @var string
     */
    protected $version = 'v1';

    /**
     * @var array
     */
    protected $tags = ['alliances'];

    /**
     * @throws \Throwable
     */
    public function handle()
    {

        $alliances = $this->retrieve();

        if ($alliances->isCachedLoad()) return;

        collect($alliances)->each(function ($alliance_id) {

            // queue another job which will be
            // responsible to collect alliance details.
            dispatch(new Info($alliance_id));

            // queue another job which will be
            // responsible to collect alliance members.
            dispatch(new Members($alliance_id));
        });
    }
}
