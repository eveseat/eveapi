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

use Exception;
use Seat\Eveapi\Jobs\EsiBase;
use Seat\Eveapi\Models\Status\EsiStatus;

/**
 * Class Status.
 * @package Seat\Eveapi\Jobs\Status
 */
class Esi extends EsiBase
{
    /**
     * @var string
     */
    protected $method = 'get';

    /**
     * @var string
     */
    protected $endpoint = '/ping';

    /**
     * @var array
     */
    protected $tags = ['ccp', 'meta', 'public'];

    /**
     * Execute the job.
     *
     * @return void
     * @throws \Exception
     * @throws \Throwable
     */
    public function handle()
    {

        $start = microtime(true);

        try {

            $status = $this->retrieve()->raw;

        } catch (Exception $exception) {

            $status = 'Request failed with: ' . $exception->getMessage();
        }

        $end = microtime(true) - $start;

        EsiStatus::create([
            'status'       => $status,
            'request_time' => $end,
        ]);
    }
}
