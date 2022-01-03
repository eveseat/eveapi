<?php

/*
 * This file is part of SeAT
 *
 * Copyright (C) 2015 to 2021 Leon Jacobs
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
use Seat\Eveapi\Models\Sovereignty\SovereigntyMap;

/**
 * Class SolarSystem.
 *
 * @package Seat\Eveapi\Models\Sde
 *
 * @OA\Schema(
 *     title="SolarSystem",
 *     type="object",
 *     description="Solar System"
 * )
 *
 * @OA\Property(
 *     property="system_id",
 *     description="Solar System Unique Identifier",
 *     type="integer",
 *     format="int64"
 * )
 *
 * @OA\Property(
 *     property="constellation_id",
 *     description="Constellation Unique Identifier",
 *     type="integer",
 *     format="int64"
 * )
 *
 * @OA\Property(
 *     property="region_id",
 *     description="Region Unique Identifier",
 *     type="integer",
 *     format="int64"
 * )
 *
 * @OA\Property(
 *     property="name",
 *     description="Solar System name",
 *     type="string"
 * )
 *
 * @OA\Propertu(
 *     property="security",
 *     description="Solar System Security Level",
 *     type="double"
 * )
 */
class SolarSystem extends Model
{
    /**
     * @var bool
     */
    public $incrementing = false;

    /**
     * @var string
     */
    protected $primaryKey = 'system_id';

    /**
     * @var string
     */
    protected $table = 'solar_systems';

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function constellation()
    {
        return $this->belongsTo(Constellation::class, 'constellation_id', 'constellation_id');
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function moons()
    {
        return $this->hasMany(Moon::class, 'system_id', 'system_id');
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function planets()
    {
        return $this->hasMany(Planet::class, 'system_id', 'system_id');
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function region()
    {
        return $this->belongsTo(Region::class, 'region_id', 'region_id');
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasOne
     */
    public function sovereignty()
    {

        return $this->hasOne(SovereigntyMap::class, 'system_id', 'system_id')
            ->withDefault();
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasOne
     */
    public function star()
    {
        return $this->hasOne(Star::class, 'system_id', 'system_id');
    }
}
