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

namespace Seat\Eveapi\Models\PlanetaryInteraction;

use Seat\Eveapi\Models\Sde\InvType;
use Seat\Eveapi\Models\Sde\Planet;
use Seat\Eveapi\Models\Sde\SolarSystem;
use Seat\Services\Models\ExtensibleModel;

/**
 * Class CharacterPlanet.
 *
 * @package Seat\Eveapi\Models\PlanetaryInteraction
 */
class CharacterPlanet extends ExtensibleModel
{
    /**
     * @var bool
     */
    protected static $unguarded = true;

    /**
     * @var string
     */
    protected $primaryKey = 'id';

    /**
     * @var bool
     */
    public $incrementing = true;

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function extractors()
    {
        return $this->hasMany(CharacterPlanetExtractor::class, 'planet_id', 'planet_id')
            ->where('character_id', $this->character_id);
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function planet()
    {
        return $this->belongsTo(Planet::class, 'planet_id', 'planet_id')
            ->withDefault([
                'type' => new InvType(),
            ]);
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function solar_system()
    {
        return $this->belongsTo(SolarSystem::class, 'solar_system_id', 'system_id')
            ->withDefault();
    }
}
