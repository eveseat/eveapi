<?php

/*
 * This file is part of SeAT
 *
 * Copyright (C) 2015, 2016, 2017, 2018, 2019, 2020  Leon Jacobs
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

namespace Seat\Eveapi\Jobs\Middleware;

use Seat\Eveapi\Exception\EsiDownException;
use Seat\Eveapi\Jobs\EsiBase;
use Seat\Eveapi\Models\Status\EsiStatus;

/**
 * Class CheckEsiStatus.
 *
 * @package Seat\Eveapi\Jobs\Middleware
 */
class CheckEsiStatus
{
    /**
     * @param \Seat\Eveapi\Jobs\EsiBase $job
     * @param $next
     * @throws \Exception
     */
    public function handle($job, $next)
    {
        // bypass control if the class is not related to ESI
        if (! is_subclass_of($job,EsiBase::class)) {
            $next($job);
            return;
        }

        // esi seems to be online - continue
        if ($this->isEsiOnline()) {
            $next($job);
            return;
        }

        // esi seems down
        $job->fail(new EsiDownException());
    }

    /**
     * @return bool
     */
    private function isEsiOnline(): bool
    {
        // Get the latest ESI status.
        $status = cache()->remember('esi_db_status', 60, function () {
            return EsiStatus::latest()->first();
        });

        logger()->debug('isEsiOnline', ['status' => $status]);

        // If we don't have a status yet, assume everything is ok.
        if (! $status) return true;

        // If the data is too old, return false by default.
        // Not being able to ping ESI could be indicative
        // of many other problems.
        if ($status->created_at->lte(carbon('now')->subHours(2)))
            return false;

        // If the status is OK, yay.
        if ($status->status == 'ok')
            return true;

        return true;
    }
}
