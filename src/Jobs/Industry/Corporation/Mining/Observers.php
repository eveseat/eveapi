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

namespace Seat\Eveapi\Jobs\Industry\Corporation;


use Seat\Eveapi\Jobs\EsiBase;
use Seat\Eveapi\Models\Industry\Mining\CorporationObserver;

/**
 * Class MiningObservers
 * @package Seat\Eveapi\Jobs\Industry\Corporation\Mining
 */
class Observers extends EsiBase
{
    /**
     * @var string
     */
    protected $method = 'get';

    /**
     * @var string
     */
    protected $endpoint = '/corporation/{corporation_id}/mining/observers/';

    /**
     * @var string
     */
    protected $version = 'v1';

    /**
     * @var int
     */
    protected $page = 1;

    /**
     * Execute the job.
     *
     * @return void
     * @throws \Exception
     */
    public function handle()
    {

        $mining_observers = $this->retrieve([
            'corporation_id' => $this->getCorporationId(),
        ]);

        collect($mining_observers)->each(function ($observer) {

            CorporationObserver::firstOrNew([
                'corporation_id' => $this->getCorporationId(),
                'observer_id'    => $observer->observer_id,
            ])->fill([
                'last_updated'  => carbon($observer->last_updated),
                'observer_type' => $observer->observer_type,
            ])->save();
        });

        CorporationObserver::where('corporation_id', $this->getCorporationId())
            ->whereNotIn('observer_id', collect($mining_observers)
                ->pluck('observer_id')->flatten()->all())
            ->delete();

        // TODO: Process mining observer details.
    }
}
