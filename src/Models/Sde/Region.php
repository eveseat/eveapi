<?php

/*
 * This file is part of SeAT
 *
 * Copyright (C) 2015 to 2022 Leon Jacobs
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

namespace Seat\Eveapi\Models\Sde;

use Illuminate\Database\Eloquent\Model;

/**
 * Class Region.
 *
 * @package Seat\Eveapi\Models\Sde
 */
class Region extends Model
{
    /**
     * @var bool
     */
    public $incrementing = false;

    /**
     * @var string
     */
    protected $primaryKey = 'region_id';

    /**
     * @var string
     */
    protected $table = 'regions';

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function constellations()
    {
        return $this->hasMany(Constellation::class, 'region_id', 'region_id');
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function moons()
    {
        return $this->hasMany(Moon::class, 'region_id', 'region_id');
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function planets()
    {
        return $this->hasMany(Planet::class, 'region_id', 'region_id');
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function solar_systems()
    {
        return $this->hasMany(SolarSystem::class, 'region_id', 'region_id');
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function stars()
    {
        return $this->hasMany(Star::class, 'region_id', 'region_id');
    }
}
