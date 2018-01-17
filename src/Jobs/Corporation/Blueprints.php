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
use Seat\Eveapi\Models\Corporation\CorporationBlueprint;
use Seat\Eveapi\Models\Corporation\CorporationInfo;
use Seat\Eveapi\Models\RefreshToken;

class Blueprints extends EsiBase {

    protected $method = 'get';

    protected $endpoint = '/corporations/{corporation_id}/blueprints/';

    protected $version = 'v1';

    protected $page = 1;

    protected $known_blueprints;

    public function __construct(RefreshToken $token = null) {
        $this->known_blueprints = collect();

        parent::__construct($token);
    }

    public function handle()
    {

        while (true)
        {

            $blueprints = $this->retrieve([
                'corporation_id' => $this->getCorporationId(),
            ]);

            collect($blueprints)->each(function($blueprint){

                CorporationBlueprint::firstOrNew([
                    'corporation_id'      => $this->getCorporationId(),
                    'item_id'             => $blueprint->item_id,
                ])->fill([
                    'type_id'             => $blueprint->type_id,
                    'location_id'         => $blueprint->location_id,
                    'location_flag'       => $blueprint->location_flag,
                    'quantity'            => $blueprint->quantity,
                    'time_efficiency'     => $blueprint->time_efficiency,
                    'material_efficiency' => $blueprint->material_efficiency,
                    'runs'                => $blueprint->runs,
                ])->save();

            });

            $this->known_blueprints->push(collect($blueprints)
                ->pluck('item_id')->flatten()->all());

            if (! $this->nextPage($blueprints->pages))
                break;

        }

        // Cleanup lost blueprints
        CorporationBlueprint::where('corporation_id', $this->getCorporationId())
            ->whereNotIn('item_id', $this->known_blueprints->flatten()->all())
            ->delete();

    }

}
