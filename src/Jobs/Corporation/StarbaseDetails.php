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
use Seat\Eveapi\Models\Corporation\CorporationStarbase;
use Seat\Eveapi\Models\Corporation\CorporationStarbaseDetail;
use Seat\Eveapi\Models\Corporation\CorporationStarbaseFuel;
use Seat\Eveapi\Models\RefreshToken;

/**
 * Class Starbase
 * @package Seat\Eveapi\Jobs\Corporation
 */
class StarbaseDetails extends EsiBase
{
    /**
     * @var string
     */
    protected $method = 'get';

    /**
     * @var string
     */
    protected $endpoint = '/corporations/{corporation_id}/starbases/{starbase_id}/';

    /**
     * @var string
     */
    protected $version = 'v1';

    /**
     * @var
     */
    protected $known_starbases;

    /**
     * Starbase constructor.
     *
     * @param RefreshToken|null $token
     */
    public function __construct(RefreshToken $token = null)
    {

        $this->known_starbases = collect();

        parent::__construct($token);
    }

    /**
     * @throws \Exception
     */
    public function handle()
    {

        CorporationStarbase::where('corporation_id', $this->getCorporationId())->get()->each(function ($starbase) {

            $this->query_string = [
                'system_id' => $starbase->system_id,
            ];

            $detail = $this->retrieve([
                'corporation_id' => $this->getCorporationId(),
                'starbase_id'    => $starbase->starbase_id,
            ]);

            CorporationStarbaseDetail::firstOrNew([
                'corporation_id' => $this->getCorporationId(),
                'starbase_id'    => $starbase->starbase_id,
            ])->fill([
                'fuel_bay_view'                            => $detail->fuel_bay_view,
                'fuel_bay_take'                            => $detail->fuel_bay_take,
                'anchor'                                   => $detail->anchor,
                'unanchor'                                 => $detail->unanchor,
                'online'                                   => $detail->online,
                'offline'                                  => $detail->offline,
                'allow_corporation_members'                => $detail->allow_corporation_members,
                'allow_alliance_members'                   => $detail->allow_alliance_members,
                'use_alliance_standings'                   => $detail->use_alliance_standings,
                'attack_standing_threshold'                => $detail->attack_standing_threshold ?? null,
                'attack_security_status_threshold'         => $detail->attack_security_status_threshold ?? null,
                'attack_if_other_security_status_dropping' => $detail->attack_if_other_security_status_dropping,
                'attack_if_at_war'                         => $detail->attack_if_at_war,
            ])->save();

            if (property_exists($detail, 'fuels'))

                collect($detail->fuels)->each(function ($fuel) use ($starbase) {

                    CorporationStarbaseFuel::firstOrNew([
                        'corporation_id' => $this->getCorporationId(),
                        'starbase_id'    => $starbase->starbase_id,
                        'type_id'        => $fuel->type_id,
                    ])->fill([
                        'quantity' => $fuel->quantity,
                    ])->save();

                });

            $this->known_starbases->push($starbase->starbase_id);

        });

        CorporationStarbaseDetail::where('corporation_id', $this->getCorporationId())
            ->whereNotIn('starbase_id', $this->known_starbases->flatten()->all())
            ->delete();

        CorporationStarbaseFuel::where('corporation_id', $this->getCorporationId())
            ->whereNotIn('starbase_id', $this->known_starbases->flatten()->all())
            ->delete();
    }
}
