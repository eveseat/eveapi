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

namespace Seat\Eveapi\Jobs\Middleware;

use Seat\Eveapi\Jobs\EsiBase;

/**
 * Class CheckTokenVersion.
 *
 * @package Seat\Eveapi\Jobs\Middleware
 */
class CheckTokenVersion
{

    const CURRENT_VERSION = 2;
    /**
     * @param \Seat\Eveapi\Jobs\EsiBase $job
     * @param $next
     */
    public function handle($job, $next)
    {

        logger()->error("TEST");
                
        // in case the job is not ESI related - bypass this check
        if (! is_subclass_of($job, EsiBase::class)) {
            $next($job);

            return;
        }

        $ver = $job->getToken()->version;
        logger()->error($ver);

        if ($ver == self::CURRENT_VERSION ){
            logger()->error('Job running with up to date token', [
                'job' => get_class($job),
                'token_id' => $job->getToken()->character_id,
            ]);
            $next($job);
        } else {
            logger()->error('Job deleted due to token version', [
                'job' => get_class($job),
                'token_id' => $job->getToken()->character_id,
            ]);
            $job->delete();
        }

    }
}