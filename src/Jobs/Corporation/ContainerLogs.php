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

namespace Seat\Eveapi\Jobs\Corporation;

use Seat\Eveapi\Jobs\EsiBase;
use Seat\Eveapi\Models\Corporation\CorporationContainerLog;

/**
 * Class ContainerLogs.
 * @package Seat\Eveapi\Jobs\Corporation
 */
class ContainerLogs extends EsiBase
{
    /**
     * @var string
     */
    protected $method = 'get';

    /**
     * @var string
     */
    protected $endpoint = '/corporations/{corporation_id}/containers/logs/';

    /**
     * @var string
     */
    protected $version = 'v2';

    /**
     * @var string
     */
    protected $scope = 'esi-corporations.read_container_logs.v1';

    /**
     * @var array
     */
    protected $roles = ['Director'];

    /**
     * @var array
     */
    protected $tags = ['corporation', 'container_logs'];

    /**
     * @var int
     */
    protected $page = 1;

    /**
     * Execute the job.
     *
     * @return void
     * @throws \Throwable
     */
    public function handle()
    {

        if (! $this->preflighted()) return;

        while (true) {

            $logs = $this->retrieve([
                'corporation_id' => $this->getCorporationId(),
            ]);

            if ($logs->isCachedLoad()) return;

            collect($logs)->each(function ($log) {

                CorporationContainerLog::firstOrNew([
                    'corporation_id' => $this->getCorporationId(),
                    'container_id'   => $log->container_id,
                    'logged_at'      => carbon($log->logged_at),
                ])->fill([
                    'container_type_id'  => $log->container_type_id,
                    'character_id'       => $log->character_id,
                    'location_id'        => $log->location_id,
                    'action'             => $log->action,
                    'location_flag'      => $log->location_flag,
                    'password_type'      => $log->password_type ?? null,
                    'type_id'            => $log->type_id ?? null,
                    'quantity'           => $log->quantity ?? null,
                    'old_config_bitmask' => $log->old_config_bitmask ?? null,
                    'new_config_bitmask' => $log->new_config_bitmask ?? null,
                ])->save();

            });

            if (! $this->nextPage($logs->pages))
                break;
        }
    }
}
