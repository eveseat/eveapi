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
use OpenApi\Annotations as OA;
use Seat\Eveapi\Models\Fittings\Insurance;
use Seat\Eveapi\Models\Market\Price;
use Seat\Eveapi\Traits\IsReadOnly;

/**
 * Class InvType.
 *
 * @package Seat\Eveapi\Models\Sde
 *
 * @OA\Property(
 *     type="integer",
 *     minimum=1,
 *     property="typeID",
 *     description="The inventory type ID"
 * )
 *
 * @OA\Property(
 *     type="integer",
 *     minimum=1,
 *     property="groupID",
 *     description="The group to which the type is related"
 * )
 *
 * @OA\Property(
 *     type="string",
 *     property="typeName",
 *     description="The inventory type name"
 * )
 *
 * @OA\Property(
 *     type="string",
 *     property="description",
 *     description="The inventory type description"
 * )
 *
 * @OA\Property(
 *     type="number",
 *     format="double",
 *     property="mass",
 *     description="The inventory type mass"
 * )
 *
 * @OA\Property(
 *     type="number",
 *     format="double",
 *     property="volume",
 *     description="The inventory type volume"
 * )
 *
 * @OA\Property(
 *     type="number",
 *     format="double",
 *     property="capacity",
 *     description="The inventory type storage capacity"
 * )
 *
 * @OA\Property(
 *     type="integer",
 *     property="portionSize"
 * )
 *
 * @OA\Property(
 *     type="integer",
 *     property="raceID",
 *     description="The race to which the inventory type is tied"
 * )
 *
 * @OA\Property(
 *     type="number",
 *     format="double",
 *     property="basePrice",
 *     description="The initial price used by NPC to create order"
 * )
 *
 * @OA\Property(
 *     type="boolean",
 *     property="published",
 *     description="True if the item is available in-game"
 * )
 *
 * @OA\Property(
 *     type="integer",
 *     property="marketGroupID",
 *     description="The group into which the item is available on market"
 * )
 *
 * @OA\Property(
 *     type="integer",
 *     property="iconID"
 * )
 *
 * @OA\Property(
 *     type="integer",
 *     property="soundID"
 * )
 *
 * @OA\Property(
 *     type="integer",
 *     property="graphicID"
 * )
 */
#[OA\Schema(
    title: 'InvType',
    description: 'Inventory Type',
    type: 'object'
)]
class InvType extends Model
{
    use IsReadOnly;

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
