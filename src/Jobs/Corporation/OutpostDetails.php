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
 * Class Outpost.
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
     * @var string
     */
    protected $scope = 'esi-corporations.read_outposts.v1';

    /**
     * @var array
     */
    protected $roles = ['Director'];

    /**
     * @var array
     */
    protected $tags = ['corporation', 'outposts', 'details'];

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
     * Execute the job.
     *
     * @throws \Throwable
     */
    public function handle()
    {

        if (! $this->preflighted()) return;

        CorporationOutpost::where('corporation_id', $this->getCorporationId())->get()
            ->each(function ($outpost) {

                $detail = $this->retrieve([
                    'corporation_id' => $this->getCorporationId(),
                    'outpost_id'     => $outpost->outpost_id,
                ]);

                // TODO: Add find_nearest_celestial() data

                CorporationOutpostDetail::firstOrNew([
                    'corporation_id' => $this->getCorporationId(),
                    'outpost_id'     => $outpost->outpost_id,
                ])->fill([
                    'owner_id'                     => $detail->owner_id,
                    'system_id'                    => $detail->system_id,
                    'docking_cost_per_ship_volume' => $detail->docking_cost_per_ship_volume,
                    'office_rental_cost'           => $detail->office_rental_cost,
                    'type_id'                      => $detail->type_id,
                    'reprocessing_efficiency'      => $detail->reprocessing_efficiency,
                    'reprocessing_station_take'    => $detail->reprocessing_station_take,
                    'standing_owner_id'            => $detail->standing_owner_id,
                    'x'                            => $detail->coordinates->x,
                    'y'                            => $detail->coordinates->y,
                    'z'                            => $detail->coordinates->z,
                ])->save();

                collect($detail->services)->each(function ($service) use ($outpost, $detail) {

                    CorporationOutpostService::firstOrNew([
                        'corporation_id' => $this->getCorporationId(),
                        'outpost_id'     => $outpost->outpost_id,
                        'service_name'   => $service->service_name,
                    ])->fill([
                        'minimum_standing'           => $service->minimum_standing,
                        'surcharge_per_bad_standing' => $service->surcharge_per_bad_standing,
                        'discount_per_good_standing' => $service->discount_per_good_standing,
                    ])->save();

                });

                CorporationOutpostService::where('corporation_id', $this->getCorporationId())
                    ->where('outpost_id', $outpost->outpost_id)
                    ->whereNotIn('service_name', collect($detail->services)
                        ->pluck('service_name')
                        ->flatten()->all())
                    ->delete();

                $this->known_outposts->push($outpost->outpost_id);

            });

        CorporationOutpostDetail::where('corporation_id', $this->getCorporationId())
            ->whereNotIn('outpost_id', $this->known_outposts->flatten()->all())
            ->delete();
    }
}
