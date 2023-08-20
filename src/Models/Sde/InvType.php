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

namespace Seat\Eveapi\Models\Sde;

use Illuminate\Database\Eloquent\Model;
use OpenApi\Attributes as OA;
use Seat\Eveapi\Models\Fittings\Insurance;
use Seat\Eveapi\Models\Market\Price;
use Seat\Eveapi\Traits\IsReadOnly;

#[OA\Schema(
    title: 'InvType',
    description: 'inventory Type',
    properties: [
        new OA\Property(property: 'typeID', description: 'The inventory type ID', type: 'integer', minimum: 1),
        new OA\Property(property: 'groupID', description: 'The group to which the type is related', type: 'integer', minimum: 1),
        new OA\Property(property: 'typeName', description: 'The inventory type name', type: 'string'),
        new OA\Property(property: 'description', description: 'The inventory type description', type: 'string'),
        new OA\Property(property: 'mass', description: 'The inventory type mass', type: 'number', format: 'double'),
        new OA\Property(property: 'volume', description: 'The inventory type volume', type: 'number', format: 'double'),
        new OA\Property(property: 'capacity', description: 'The inventory type storage capacity', type: 'number', format: 'double'),
        new OA\Property(property: 'portionSize', type: 'integer'),
        new OA\Property(property: 'raceID', description: 'The race to which the inventory type is tied', type: 'integer'),
        new OA\Property(property: 'basePrice', description: 'The initial price used by NPC to create order', type: 'number', format: 'double'),
        new OA\Property(property: 'published', description: 'True if the item is available on market', type: 'boolean'),
        new OA\Property(property: 'marketGroupID', description: 'The group into which the item is available on market', type: 'integer'),
        new OA\Property(property: 'iconID', type: 'integer'),
        new OA\Property(property: 'soundID', type: 'integer'),
        new OA\Property(property: 'graphicID', type: 'integer'),
    ],
    type: 'object'
)]
class InvType extends Model
{
    use IsReadOnly;

    /**
     * Maximum value a skill of rank 1 may have when level 5 has been reached.
     */
    const MAX_SKILL_SKILLPOINTS = 256000;

    /**
     * @var bool
     */
    public $incrementing = false;

    /**
     * @var array
     */
    protected $casts = [
        'published' => 'boolean',
    ];

    /**
     * @var string
     */
    protected $table = 'invTypes';

    /**
     * @var string
     */
    protected $primaryKey = 'typeID';

    /**
     * @var bool
     */
    public $timestamps = false;

    /**
     * The maximum amount of skillpoints when level 5 has been reached for current skill.
     *
     * @return int
     */
    public function getMaximumSkillpointsAttribute()
    {
        return round($this->dogma_attributes->where('attributeID', DgmTypeAttribute::SKILL_RANK_ID)->first()->valueFloat) * self::MAX_SKILL_SKILLPOINTS;
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
     */
    public function materials()
    {
        return $this->belongsToMany(InvType::class, 'invTypeMaterials', 'typeID', 'materialTypeID')
            ->withPivot('quantity');
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function group()
    {

        return $this->belongsTo(InvGroup::class, 'groupID', 'groupID')
            ->withDefault([
                'groupName' => trans('web::seat.unknown'),
            ]);
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasOne
     */
    public function price()
    {

        return $this->hasOne(Price::class, 'type_id', 'typeID')
            ->withDefault([
                'average'        => 0.00,
                'average_price'  => 0.00,
                'adjusted_price' => 0.00,
            ]);
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function insurances()
    {
        return $this->hasMany(Insurance::class, 'type_id', 'typeID');
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function dogma_attributes()
    {
        return $this->hasMany(DgmTypeAttribute::class, 'typeID', 'typeID');
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
     */
    public function reactions()
    {
        return $this->belongsToMany(InvType::class, 'invTypeReactions', 'typeID', 'reactionTypeID')
            ->withPivot('input', 'quantity');
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
     */
    public function components()
    {
        return $this->belongsToMany(InvType::class, 'invTypeReactions', 'reactionTypeID', 'typeID')
            ->wherePivot('input', true)
            ->withPivot('input', 'quantity');
    }
}
