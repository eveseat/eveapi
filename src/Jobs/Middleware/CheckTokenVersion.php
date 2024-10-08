<?php

/*
 * This file is part of SeAT
 *
 * Copyright (C) 2015 to present Leon Jacobs
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

use Closure;
use Seat\Eveapi\Exception\TokenVersionException;
use Seat\Eveapi\Jobs\EsiBase;
use Seat\Eveapi\Models\RefreshToken;

/**
 * Class CheckTokenVersion.
 *
 * @package Seat\Eveapi\Jobs\Middleware
 */
class CheckTokenVersion
{
    /**
     * @param  \Seat\Eveapi\Jobs\EsiBase  $job
     * @param  \Closure  $next
     * @return void
     */
    public function handle(EsiBase $job, Closure $next): void
    {
        $version = $job->getToken()->version;

        if ($version == RefreshToken::CURRENT_VERSION){
            logger()->debug(
                sprintf('[Jobs][Middlewares][%s] Check Token Version -> Processing job is using up to date token.', $job->job->getJobId()),
                [
                    'fqcn' => get_class($job),
                    'character_id' => $job->getToken()->character_id,
                ]);

            $next($job);

            return;
        }

        logger()->error(
            sprintf('[Jobs][Middlewares][%s] Check Token Version -> Deleting job due to outdated token version.', $job->job->getJobId()),
            [
                'fqcn' => get_class($job),
                'character_id' => $job->getToken()->character_id,
            ]);

        $job->tries = 1;
        $job->fail(new TokenVersionException('Token Version Mismatch. Run command `php artisan seat:token:upgrade` in order to fix.'));
    }
}
