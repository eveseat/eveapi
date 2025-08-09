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

namespace Seat\Eveapi\Jobs\PlanetaryInteraction\Corporation;

use Seat\Eveapi\Jobs\AbstractAuthCorporationJob;
use Seat\Eveapi\Models\PlanetaryInteraction\CorporationCustomsOffice;
use Seat\Eveapi\Models\Sde\MapDenormalize;
use Seat\Eveapi\Traits\Utils;

/**
 * Class CustomsOffices.
 *
 * @package Seat\Eveapi\Jobs\Corporation
 */
class CustomsOfficeLocations extends AbstractAuthCorporationJob
{
    use Utils;

    /**
     * @var string
     */
    protected $method = 'post';

    /**
     * @var string
     */
    protected $endpoint = '/corporations/{corporation_id}/assets/locations/';

    /**
     * @var string
     */
    protected string $compatibility_date = "2025-07-20";

    /**
     * @var string
     */
    protected $scope = 'esi-assets.read_assets.v1';

    /**
     * @var array
     */
    protected $roles = ['Director'];

    /**
     * @var array
     */
    protected $tags = ['corporation', 'pi'];

    /**
     * Execute the job.
     *
     * @return void
     *
     * @throws \Throwable
     */
    public function handle()
    {
        parent::handle();

        $customs_offices = CorporationCustomsOffice::where('corporation_id', $this->getCorporationId())->get();

        collect($customs_offices)->chunk(1000)->each(function ($chunk) {

            $this->request_body = $chunk->map(function ($office) {

                return $office->office_id;
            })->flatten()->toArray();

            $response = $this->retrieve([
                'corporation_id' => $this->getCorporationId(),
            ]);

            $locations = $response->getBody();

            collect($locations)->each(function ($location) use ($chunk) {

                $nearest_celestial = $this->find_nearest_celestial(
                    $chunk->firstWhere('office_id', $location->item_id)->system_id,
                    $location->position->x,
                    $location->position->y,
                    $location->position->z,
                    MapDenormalize::PLANET
                );

                CorporationCustomsOffice::firstOrNew([
                    'corporation_id' => $this->getCorporationId(),
                    'office_id' => $location->item_id,
                ])->fill([
                    'x' => $location->position->x,
                    'y' => $location->position->y,
                    'z' => $location->position->z,
                    'location_id' => $nearest_celestial['map_id'],
                ])->save();

            });

        });
    }
}
