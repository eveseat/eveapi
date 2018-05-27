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
use Seat\Eveapi\Models\PlanetaryInteraction\CorporationCustomsOffice;
use Seat\Eveapi\Models\RefreshToken;

/**
 * Class CustomsOffices.
 * @package Seat\Eveapi\Jobs\Corporation
 */
class CustomsOffices extends EsiBase
{
    /**
     * @var string
     */
    protected $method = 'get';

    /**
     * @var string
     */
    protected $endpoint = '/corporations/{corporation_id}/customs_offices/';

    /**
     * @var string
     */
    protected $version = 'v1';

    /**
     * @var string
     */
    protected $scope = 'esi-planets.read_customs_offices.v1';

    /**
     * @var array
     */
    protected $roles = ['Director'];

    /**
     * @var array
     */
    protected $tags = ['corporation', 'customs_offices'];

    /**
     * @var int
     */
    protected $page = 1;

    /**
     * @var \Illuminate\Support\Collection
     */
    protected $known_structures;

    /**
     * CustomsOffices constructor.
     *
     * @param \Seat\Eveapi\Models\RefreshToken|null $token
     */
    public function __construct(RefreshToken $token = null)
    {

        $this->known_structures = collect();

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

        while (true) {

            $customs_offices = $this->retrieve([
                'corporation_id' => $this->getCorporationId(),
            ]);

            if ($customs_offices->isCachedLoad()) return;

            collect($customs_offices)->each(function ($customs_office) {

                CorporationCustomsOffice::firstOrNew([
                    'corporation_id' => $this->getCorporationId(),
                    'office_id'      => $customs_office->office_id,
                ])->fill([
                    'system_id'                   => $customs_office->system_id,
                    'reinforce_exit_start'        => $customs_office->reinforce_exit_start,
                    'reinforce_exit_end'          => $customs_office->reinforce_exit_end,
                    'corporation_tax_rate'        => $customs_office->corporation_tax_rate ?? null,
                    'allow_alliance_access'       => $customs_office->allow_alliance_access,
                    'alliance_tax_rate'           => $customs_office->alliance_tax_rate ?? null,
                    'allow_access_with_standings' => $customs_office->allow_access_with_standings,
                    'standing_level'              => $customs_office->standing_level ?? null,
                    'excellent_standing_tax_rate' => $customs_office->excellent_standing_tax_rate ?? null,
                    'good_standing_tax_rate'      => $customs_office->good_standing_tax_rate ?? null,
                    'neutral_standing_tax_rate'   => $customs_office->neutral_standing_tax_rate ?? null,
                    'bad_standing_tax_rate'       => $customs_office->bad_standing_tax_rate ?? null,
                    'terrible_standing_tax_rate'  => $customs_office->terrible_standing_tax_rate ?? null,
                ])->save();

                $this->known_structures->push($customs_office->office_id);

            });

            if (! $this->nextPage($customs_offices->pages))
                break;
        }

        // Cleanup customs offices that were not in the response.
        CorporationCustomsOffice::where('corporation_id', $this->getCorporationId())
            ->whereNotIn('office_id', $this->known_structures->flatten()->all())
            ->delete();
    }
}
