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
use Seat\Eveapi\Models\Corporation\CorporationOutpost;
use Seat\Eveapi\Models\Corporation\CorporationOutpostDetail;
use Seat\Eveapi\Models\Corporation\CorporationOutpostService;
use Seat\Eveapi\Models\RefreshToken;

/**
 * Class Outpost
 * @package Seat\Eveapi\Jobs\Corporation
 */
class OutpostDetails extends EsiBase
{
    /**
     * @var string
     */
    protected $method = 'get';

    /**
     * @var string
     */
    protected $endpoint = '/corporations/{corporation_id}/outposts/{outpost_id}/';

    /**
     * @var string
     */
    protected $version = 'v1';

    /**
     * @var int
     */
    protected $page = 1;

    /**
     * @var \Illuminate\Support\Collection
     */
    protected $known_outposts;

    /**
     * Outposts constructor.
     *
     * @param RefreshToken|null $token
     */
    public function __construct(RefreshToken $token = null)
    {

        $this->known_outposts = collect();

        parent::__construct($token);
    }

    /**
     * @throws \Exception
     */
    public function handle()
    {

        CorporationOutpost::where('corporation_id', $this->getCorporationId())->get()
            ->each(function ($outpost_id) {

                $outpost = $this->retrieve([
                    'corporation_id' => $this->getCorporationId(),
                    'outpost_id'     => $outpost_id,
                ]);

                // TODO: Add find_nearest_celestial() data

                CorporationOutpostDetail::firstOrNew([
                    'corporation_id' => $this->getCorporationId(),
                    'outpost_id'     => $outpost_id,
                ])->fill([
                    'owner_id'                     => $outpost->owner_id,
                    'system_id'                    => $outpost->system_id,
                    'docking_cost_per_ship_volume' => $outpost->docking_cost_per_ship_volume,
                    'office_rental_cost'           => $outpost->office_rental_cost,
                    'type_id'                      => $outpost->type_id,
                    'reprocessing_efficiency'      => $outpost->reprocessing_efficiency,
                    'reprocessing_station_take'    => $outpost->reprocessing_station_take,
                    'standing_owner_id'            => $outpost->standing_owner_id,
                    'x'                            => $outpost->coordinates->x,
                    'y'                            => $outpost->coordinates->y,
                    'z'                            => $outpost->coordinates->z,
                ])->save();

                collect($outpost->services)->each(function ($service) use ($outpost_id, $outpost) {

                    CorporationOutpostService::firstOrNew([
                        'corporation_id' => $this->getCorporationId(),
                        'outpost_id'     => $outpost_id,
                        'service_name'   => $service->service_name,
                    ])->fill([
                        'minimum_standing'           => $service->minimum_standing,
                        'surcharge_per_bad_standing' => $service->surcharge_per_bad_standing,
                        'discount_per_good_standing' => $service->discount_per_good_standing,
                    ])->save();

                });

                CorporationOutpostService::where('corporation_id', $this->getCorporationId())
                    ->where('outpost_id', $outpost_id)
                    ->whereNotIn('service_name', collect($outpost->services)
                        ->pluck('service_name')
                        ->flatten()->all())
                    ->delete();

                $this->known_outposts->push($outpost_id);

            });

        CorporationOutpostDetail::where('corporation_id', $this->getCorporationId())
            ->whereNotIn('outpost_id', $this->known_outposts->flatten()->all())
            ->delete();
    }
}
