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
use Seat\Eveapi\Traits\IsReadOnly;

/**
 * Class MapDenormalize.
 *
 * @package Seat\Eveapi\Models\Sde
 *
 * @OA\Schema(
 *     description="Map Denormalize",
 *     title="MapDenormalize",
 *     type="object"
 * )
 *
 * @OA\Property(
 *     type="integer",
 *     property="itemID",
 *     description="The entity ID"
 * )
 *
 * @OA\Property(
 *     type="integer",
 *     property="typeID",
 *     description="The type of the entity"
 * )
 *
 * @OA\Property(
 *     type="integer",
 *     property="groupID",
 *     description="The group to which the entity is related"
 * )
 *
 * @OA\Property(
 *     type="integer",
 *     property="solarSystemID",
 *     description="The system to which the entity is attached"
 * )
 *
 * @OA\Property(
 *     type="integer",
 *     property="constellationID",
 *     description="The constellation to which the entity is attached"
 * )
 *
 * @OA\Property(
 *     type="integer",
 *     property="regionID",
 *     description="The region to which the entity is attached"
 * )
 *
 * @OA\Property(
 *     type="integer",
 *     property="orbitID",
 *     description="The orbit to which the entity is depending"
 * )
 *
 * @OA\Property(
 *     type="number",
 *     format="double",
 *     property="x",
 *     description="x position on the map"
 * )
 *
 * @OA\Property(
 *     type="number",
 *     format="double",
 *     property="y",
 *     description="y position on the map"
 * )
 *
 * @OA\Property(
 *     type="number",
 *     format="double",
 *     property="z",
 *     description="z position on the map"
 * )
 *
 * @OA\Property(
 *     type="number",
 *     format="double",
 *     property="radius",
 *     description="The radius of the entity"
 * )
 *
 * @OA\Property(
 *     type="string",
 *     property="itemName",
 *     description="The entity name"
 * )
 *
 * @OA\Property(
 *     type="number",
 *     format="double",
 *     property="security",
 *     description="The security status of the system to which entity is attached"
 * )
 *
 * @OA\Property(
 *     type="integer",
 *     property="celestialIndex",
 * )
 *
 * @OA\Property(
 *     type="integer",
 *     property="orbitIndex"
 * )
 */
class MapDenormalize extends Model
{
    use IsReadOnly;

    const BELT = 9;

    const CONSTELLATION = 4;

    const MOON = 8;

    const PLANET = 7;

    const REGION = 3;

    const STATION = 15;

    const SUN = 6;

    const SYSTEM = 5;

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
    protected $table = 'mapDenormalize';

    /**
     * @var string
     */
    protected $primaryKey = 'itemID';

    /**
     * @var object
     */
    private $moon_indicators;

    /**
     * Return constellations entities.
     *
     * @param  \Illuminate\Database\Eloquent\Builder  $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeConstellations($query)
    {
        return $query->where('groupID', self::CONSTELLATION);
    }

    /**
     * Return moons entities.
     *
     * @param  \Illuminate\Database\Eloquent\Builder  $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeMoons($query)
    {
        return $query->where('groupID', self::MOON);
    }

    /**
     * Return planets entities.
     *
     * @param  \Illuminate\Database\Eloquent\Builder  $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopePlanets($query)
    {
        return $query->where('groupID', self::PLANET);
    }

    /**
     * Return regions entities.
     *
     * @param  \Illuminate\Database\Eloquent\Builder  $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeRegions($query)
    {
        return $query->where('groupID', self::REGION);
    }

    /**
     * Return systems entities.
     *
     * @param  \Illuminate\Database\Eloquent\Builder  $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeSystems($query)
    {
        return $query->where('groupID', self::SYSTEM);
    }

    /**
     * @param  \Illuminate\Database\Eloquent\Builder  $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeUbiquitous($query)
    {
        return $query->whereHas('moon_content', function ($sub_query) {
            $sub_query->where('marketGroupID', self::UBIQUITOUS);
        });
    }

    /**
     * @param  \Illuminate\Database\Eloquent\Builder  $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeCommon($query)
    {
        return $query->whereHas('moon_content', function ($sub_query) {
            $sub_query->where('marketGroupID', self::COMMON);
        });
    }

    /**
     * @param  \Illuminate\Database\Eloquent\Builder  $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeUncommon($query)
    {
        return $query->whereHas('moon_content', function ($sub_query) {
            $sub_query->where('marketGroupID', self::UNCOMMON);
        });
    }

    /**
     * @param  \Illuminate\Database\Eloquent\Builder  $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeRare($query)
    {
        return $query->whereHas('moon_content', function ($sub_query) {
            $sub_query->where('marketGroupID', self::RARE);
        });
    }

    /**
     * @param  \Illuminate\Database\Eloquent\Builder  $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeExceptional($query)
    {
        return $query->whereHas('moon_content', function ($sub_query) {
            $sub_query->where('marketGroupID', self::EXCEPTIONAL);
        });
    }

    /**
     * @param  \Illuminate\Database\Eloquent\Builder  $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeStandard($query)
    {
        return $query->whereHas('moon_content', function ($sub_query) {
            $sub_query->whereNotIn('marketGroupID', [self::UBIQUITOUS, self::COMMON, self::UNCOMMON, self::RARE, self::EXCEPTIONAL]);
        });
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo|null
     */
    public function constellation()
    {
        return $this->belongsTo(MapDenormalize::class, 'constellationID', 'itemID', 'constellations')
            ->withDefault([
                'itemName' => trans('web::seat.unknown'),
            ]);
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
     */
    public function moon_content()
    {
        return $this->belongsToMany(InvType::class, 'universe_moon_contents', 'moon_id', 'type_id')
            ->withPivot('rate');
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function planet()
    {
        return $this->belongsTo(MapDenormalize::class, 'orbitID', 'itemID', 'planets')
            ->withDefault([
                'itemName' => trans('web::seat.unknown'),
            ]);
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo|null
     */
    public function region()
    {
        return $this->belongsTo(MapDenormalize::class, 'regionID', 'itemID', 'regions')
            ->withDefault([
                'itemName' => trans('web::seat.unknown'),
            ]);
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasOne
     */
    public function sovereignty()
    {

        return $this->hasOne(SovereigntyMap::class, 'system_id', 'itemID')
            ->withDefault();
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo|null
     */
    public function system()
    {

        return $this->belongsTo(MapDenormalize::class, 'solarSystemID', 'itemID', 'systems')
            ->withDefault([
                'itemName' => trans('web::seat.unknown'),
            ]);
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function type()
    {

        return $this->belongsTo(InvType::class, 'typeID', 'typeID');
    }

    /**
     * @return object
     */
    public function getMoonIndicatorsAttribute()
    {
        if (is_null($this->moon_indicators)) {
            $this->moon_indicators = (object) [
                'ubiquitous' => $this->moon_content->filter(function ($type) {
                    return $type->marketGroupID == 2396;
                })->count(),
                'common' => $this->moon_content->filter(function ($type) {
                    return $type->marketGroupID == 2397;
                })->count(),
                'uncommon' => $this->moon_content->filter(function ($type) {
                    return $type->marketGroupID == 2398;
                })->count(),
                'rare' => $this->moon_content->filter(function ($type) {
                    return $type->marketGroupID == 2400;
                })->count(),
                'exceptional' => $this->moon_content->filter(function ($type) {
                    return $type->marketGroupID == 2401;
                })->count(),
                'standard' => $this->moon_content->filter(function ($type) {
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
     * @return int
     */
    public function getStructureIdAttribute()
    {
        return $this->structure_id;
    }

    /**
     * @return string
     */
    public function getNameAttribute()
    {
        return $this->itemName;
    }

    /**
     * @return bool
     */
    public function isConstellation(): bool
    {
        return $this->groupID === self::CONSTELLATION;
    }

    /**
     * @return bool
     */
    public function isRegion(): bool
    {
        return $this->groupID === self::REGION;
    }

    /**
     * @return bool
     */
    public function isSystem(): bool
    {
        return $this->groupID === self::SYSTEM;
    }

    /**
     * @return bool
     */
    public function isSun(): bool
    {
        return $this->groupID === self::SUN;
    }

    /**
     * @return bool
     */
    public function isPlanet(): bool
    {
        return $this->groupID === self::PLANET;
    }

    /**
     * @return bool
     */
    public function isMoon(): bool
    {
        return $this->groupID === self::MOON;
    }
}
