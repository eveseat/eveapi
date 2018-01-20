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

namespace Seat\Eveapi\Jobs\PlanetaryInteraction\Corporation;

use Seat\Eveapi\Jobs\EsiBase;
use Seat\Eveapi\Models\PlanetaryInteraction\CorporationCustomOffice;
use Seat\Eveapi\Models\RefreshToken;

class CustomsOffices extends EsiBase
{

    // TODO : has to be tested

    protected $method = 'get';

    protected $endpoint = '/corporations/{corporation_id}/customs_offices/';

    protected $version = 'v1';

    protected $page = 1;

    protected $known_offices;

    public function __construct(RefreshToken $token = null)
    {
        $this->known_offices = collect();

        parent::__construct($token);
    }

    public function handle()
    {

        while (true) {

            $offices = $this->retrieve([
                'corporation_id' => $this->getCorporationId(),
            ]);

            collect($offices)->each(function($office) {

                CorporationCustomOffice::firstOrNew([
                    'corporation_id'              => $this->getCorporationId(),
                    'office_id'                   => $office->office_id,
                ])->fill([
                    'system_id'                   => $office->system_id,
                    'reinforce_exit_start'        => $office->reinforce_exit_start,
                    'reinforce_exit_end'          => $office->reinforce_exit_end,
                    'corporation_tax_rate'        => $office->corporation_tax_rate,
                    'allow_alliance_access'       => $office->allow_alliance_access,
                    'alliance_tax_rate'           => $office->alliance_tax_rate,
                    'allow_access_with_standings' => $office->allow_access_with_standings,
                    'standing_level'              => $office->standing_level,
                    'excellent_standing_tax_rate' => $office->excellent_standing_tax_rate,
                    'good_standing_tax_rate'      => $office->good_standing_tax_rate,
                    'neutral_standing_tax_rate'   => $office->neutral_standing_tax_rate,
                    'bad_standing_tax_rate'       => $office->bad_standing_tax_rate,
                    'terrible_standing_tax_rate'  => $office->terrible_standing_tax_rate,
                ])->save();

                $this->known_offices->push($office->office_id);

            });

            if (! $this->nextPage($offices->pages))
                break;

        }

        CorporationCustomOffice::where('corporation_id', $this->getCorporationId())
                               ->whereNotIn('office_id', $this->known_offices->flatten()->all())
                               ->delete();

    }

}
