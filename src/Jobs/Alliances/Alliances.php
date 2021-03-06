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

namespace Seat\Eveapi\Jobs\Alliances;

use Seat\Eveapi\Jobs\EsiBase;
use Seat\Eveapi\Models\Alliances\Alliance;

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
    protected $tags = ['alliance'];

    /**
     * @throws \Throwable
     */
    public function handle()
    {

        $alliances = $this->retrieve();

        if ($alliances->isCachedLoad() && Alliance::count() > 0)
            return;

        collect($alliances)->each(function ($alliance_id) {

            // queue jobs which will take care of this alliance update.
            Info::withChain([
                new Members($alliance_id),
            ])->dispatch($alliance_id)->delay(now()->addSeconds(rand(20, 300)));
            // in order to prevent ESI to receive massive income of all existing SeAT instances in the world
            // add a bit of randomize when job can be processed - we use seconds here, so we have more flexibility
            // https://github.com/eveseat/seat/issues/731
        });
    }
}
