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

namespace Seat\Eveapi\Jobs\Industry\Corporation\Mining;

use Seat\Eveapi\Jobs\EsiBase;
use Seat\Eveapi\Models\Industry\CorporationIndustryMiningObserver;
use Seat\Eveapi\Models\Industry\CorporationIndustryMiningObserverData;

/**
 * Class ObserverDetails.
 * @package Seat\Eveapi\Jobs\Industry\Corporation\Mining
 */
class ObserverDetails extends EsiBase
{
    /**
     * @var string
     */
    protected $method = 'get';

    /**
     * @var string
     */
    protected $endpoint = '/corporation/{corporation_id}/mining/observers/{observer_id}/';

    /**
     * @var string
     */
    protected $version = 'v1';

    /**
     * TODO: Add a local scope override as this is diff.
     *
     * @var string
     */
    protected $scope = 'esi-industry.read_corporation_mining.v1';

    /**
     * @var array
     */
    protected $roles = ['Accountant'];

    /**
     * @var array
     */
    protected $tags = ['corporation', 'mining', 'observers', 'details'];

    /**
     * @var int
     */
    protected $page = 1;

    /**
     * Execute the job.
     *
     * @throws \Throwable
     */
    public function handle()
    {

        if (! $this->preflighted()) return;

        CorporationIndustryMiningObserver::where('corporation_id', $this->getCorporationId())
            ->get()->each(function ($observer) {

                while (true) {

                    $detail = $this->retrieve([
                        'corporation_id' => $this->getCorporationId(),
                        'observer_id'    => $observer->observer_id,
                    ]);

                    if ($detail->isCachedLoad()) return;

                    collect($detail)->each(function ($data) use ($observer) {

                        CorporationIndustryMiningObserverData::firstOrNew([
                            'corporation_id'          => $this->getCorporationId(),
                            'observer_id'             => $observer->observer_id,
                            'recorded_corporation_id' => $data->recorded_corporation_id,
                            'character_id'            => $data->character_id,
                            'type_id'                 => $data->type_id,
                        ])->fill([
                            'last_updated' => $data->last_updated,
                            'quantity'     => $data->quantity,
                        ])->save();

                    });

                    if (! $this->nextPage($detail->pages))
                        break;

                }

                $this->page = 1;
            });
    }
}
