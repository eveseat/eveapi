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
use Seat\Eveapi\Models\RefreshToken;

/**
 * Class Starbases
 * @package Seat\Eveapi\Jobs\Corporation
 */
class Starbases extends EsiBase
{
    /**
     * @var string
     */
    protected $method = 'get';

    /**
     * @var string
     */
    protected $endpoint = '/corporations/{corporation_id}/starbases/';

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
    protected $known_starbases;

    /**
     * Starbases constructor.
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

        while (true) {

            $starbases = $this->retrieve([
                'corporation_id' => $this->getCorporationId(),
            ]);

            collect($starbases)->each(function ($starbase) {

                CorporationStarbase::firstOrNew([
                    'corporation_id' => $this->getCorporationId(),
                    'starbase_id'    => $starbase->starbase_id,
                ])->fill([
                    'moon_id'          => $starbase->moon_id ?? null,
                    'onlined_since'    => property_exists($starbase, 'onlined_since') ?
                        carbon($starbase->onlined_since) : null,
                    'reinforced_until' => property_exists($starbase, 'reinforced_until') ?
                        carbon($starbase->reinforced_until) : null,
                    'state'            => $starbase->state ?? null,
                    'type_id'          => $starbase->type_id,
                    'system_id'        => $starbase->system_id,
                    'unanchor_at'      => property_exists($starbase, 'unanchor_at') ?
                        carbon($starbase->unanchor_at) : null,
                ])->save();

                $this->known_starbases->push($starbase->starbase_id);

            });

            if (! $this->nextPage($starbases->pages))
                break;
        }

        CorporationStarbase::where('corporation_id', $this->getCorporationId())
            ->whereNotIn('starbase_id', $this->known_starbases->flatten()->all())
            ->delete();
    }
}
