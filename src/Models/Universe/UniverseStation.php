<?php

/*
 * This file is part of SeAT
 *
 * Copyright (C) 2015 to 2020 Leon Jacobs
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

namespace Seat\Eveapi\Models\Universe;

use Illuminate\Database\Eloquent\Model;
use Seat\Eveapi\Models\Contracts\ContractDetail;
use Seat\Eveapi\Models\Sde\SolarSystem;

/**
 * Class UniverseStation.
 * @package Seat\Eveapi\Models\Universe
 */
class UniverseStation extends Model
{
    /**
     * Those stations might be returned by ESI on some endpoints (ie: corporation infos) - however, they don't exist.
     */
    const FAKE_STATION_ID = [60000001];

    /**
     * https://github.com/esi/esi-docs/blob/master/docs/id_ranges.md.
     */
    const STATION_RANGES = [
        [60000000, 60999999], // NPC Stations
        [61000000, 63999999], // Outposts
        [68000000, 68999999], // Station folders
        [69000000, 69999999], // Outposts folders
    ];

    /**
     * @var bool
     */
    protected static $unguarded = true;

    /**
     * @var bool
     */
    public $incrementing = false;

    /**
     * @var string
     */
    protected $primaryKey = 'station_id';

    /**
     * @return \Illuminate\Database\Eloquent\Relations\MorphMany
     */
    public function contracts_from()
    {
        return $this->morphMany(ContractDetail::class, 'start_location');
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\MorphMany
     */
    public function contracts_to()
    {
        return $this->morphMany(ContractDetail::class, 'end_location');
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function solar_system()
    {
        return $this->belongsTo(SolarSystem::class, 'system_id', 'system_id')
            ->withDefault();
    }
}
