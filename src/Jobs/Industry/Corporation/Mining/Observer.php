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

class Observer extends EsiBase
{

    protected $method = 'get';

    protected $endpoint = '/corporations/{corporation_id}/observers/{observer_id}/';

    protected $version = 'v1';

    protected $page = 1;

    public function handle()
    {

        CorporationIndustryMiningObserver::where('corporation_id', $this->getCorporationId())
            ->get()->each(function($observer){

               while (true) {

                   $detail = $this->retrieve([
                       'corporation_id' => $this->getCorporationId(),
                       'observer_id'    => $observer->observer_id,
                   ]);

                   collect($detail)->each(function($data) use ($observer) {

                       CorporationIndustryMiningObserverData::firstOrNew([
                           'corporation_id'          => $this->getCorporationId(),
                           'observer_id'             => $observer->observer_id,
                           'recorded_corporation_id' => $data->recorded_corporation_id,
                           'character_id'            => $data->character_id,
                           'type_id'                 => $data->type_id,
                       ])->fill([
                           'last_updated'            => $data->last_updated,
                           'quantity'                => $data->quantity,
                       ])->save();

                   });

                   if (! $this->nextPage($detail->pages))
                       break;

               }

               $this->page = 1;

            });

    }

}