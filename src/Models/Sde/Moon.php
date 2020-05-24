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

namespace Seat\Eveapi\Models\Sde;

use Illuminate\Database\Eloquent\Model;

/**
 * Class Moon.
 *
 * @package Seat\Eveapi\Models\Sde
 */
class Moon extends Model
{
    const UBIQUITOUS = 2396;

    const COMMON = 2397;

    const UNCOMMON = 2398;

    const RARE = 2400;

    const EXCEPTIONAL = 2401;

    /**
     * @var bool
     */
    public $incrementing = false;

    /**
     * @var string
     */
    protected $primaryKey = 'moon_id';

    /**
     * @var string
     */
    protected $table = 'moons';

    /**
     * @var object
     */
    private $moon_indicators;

    /**
     * @return object
     */
    public function getMoonIndicatorsAttribute()
    {
        if (is_null($this->moon_indicators)) {
            $this->moon_indicators = (object) [
                'ubiquitous' => $this->content->filter(function ($type) {
                    return $type->marketGroupID == 2396;
                })->count(),
                'common' => $this->content->filter(function ($type) {
                    return $type->marketGroupID == 2397;
                })->count(),
                'uncommon' => $this->content->filter(function ($type) {
                    return $type->marketGroupID == 2398;
                })->count(),
                'rare' => $this->content->filter(function ($type) {
                    return $type->marketGroupID == 2400;
                })->count(),
                'exceptional' => $this->content->filter(function ($type) {
                    return $type->marketGroupID == 2401;
                })->count(),
                'standard' => $this->content->filter(function ($type) {
                    return ! in_array($type->marketGroupID, [2396, 2397, 2398, 2400, 2401]);
                })->count(),
            ];
        }

        return $this->moon_indicators ?: (object) [
            'ubiquitous' => 0,
            'common' => 0,
            'uncommon' => 0,
            'rare' => 0,
            'exceptional' => 0,
            'standard' => 0,
        ];
    }

    /**
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeUbiquitous($query)
    {
        return $query->whereHas('content', function ($sub_query) {
            $sub_query->where('marketGroupID', self::UBIQUITOUS);
        });
    }

    /**
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeCommon($query)
    {
        return $query->whereHas('content', function ($sub_query) {
            $sub_query->where('marketGroupID', self::COMMON);
        });
    }

    /**
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeUncommon($query)
    {
        return $query->whereHas('content', function ($sub_query) {
            $sub_query->where('marketGroupID', self::UNCOMMON);
        });
    }

    /**
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeRare($query)
    {
        return $query->whereHas('content', function ($sub_query) {
            $sub_query->where('marketGroupID', self::RARE);
        });
    }

    /**
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeExceptional($query)
    {
        return $query->whereHas('content', function ($sub_query) {
            $sub_query->where('marketGroupID', self::EXCEPTIONAL);
        });
    }

    /**
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeStandard($query)
    {
        return $query->whereHas('content', function ($sub_query) {
            $sub_query->whereNotIn('marketGroupID', [self::UBIQUITOUS, self::COMMON, self::UNCOMMON, self::RARE, self::EXCEPTIONAL]);
        });
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function constellation()
    {
        return $this->belongsTo(Constellation::class, 'constellation_id', 'constellation_id');
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
     */
    public function content()
    {
        return $this->belongsToMany(InvType::class, 'universe_moon_contents', 'moon_id', 'type_id')
            ->withPivot('rate');
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function planet()
    {
        return $this->belongsTo(Planet::class, 'planet_id', 'planet_id');
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function region()
    {
        return $this->belongsTo(Region::class, 'region_id', 'region_id');
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function solar_system()
    {
        return $this->belongsTo(SolarSystem::class, 'system_id', 'system_id');
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function star()
    {
        return $this->belongsTo(Star::class, 'system_id', 'system_id');
    }
}
