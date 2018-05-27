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

namespace Seat\Eveapi\Jobs\Status;

use Seat\Eveapi\Jobs\EsiBase;
use Seat\Eveapi\Models\Status\ServerStatus;

/**
 * Class Status.
 * @package Seat\Eveapi\Jobs\Status
 */
class Status extends EsiBase
{
    /**
     * @var string
     */
    protected $method = 'get';

    /**
     * @var string
     */
    protected $endpoint = '/status/';

    /**
     * @var int
     */
    protected $version = 'v1';

    /**
     * @var array
     */
    protected $tags = ['ccp', 'public'];

    /**
     * Execute the job.
     *
     * @return void
     * @throws \Exception
     * @throws \Throwable
     */
    public function handle()
    {

        $status = $this->retrieve();

        if ($status->isCachedLoad()) return;

        $latest_status = ServerStatus::latest()->first();

        // The endpoint caches for 30 seconds, so make sure we
        // don't add more entries before that cache has expired.
        if (! $latest_status || $latest_status->created_at->addSeconds(30)
                ->lt(carbon())) {

            ServerStatus::create([
                'start_time'     => carbon($status->start_time),
                'players'        => $status->players,
                'server_version' => $status->server_version,
                'vip'            => property_exists($status, 'vip') ?
                    $status->vip : false,
            ]);
        }
    }
}
