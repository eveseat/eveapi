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

namespace Seat\Eveapi\Models\Market;

use Illuminate\Database\Eloquent\Model;
use OpenApi\Attributes as OA;
use Seat\Eveapi\Models\Sde\InvType;
use Seat\Eveapi\Models\Universe\UniverseStructure;

#[OA\Schema(
    title: 'CorporationOrder',
    description: 'Corporation Order',
    properties: [
        new OA\Property(property: 'order_id', description: 'The market order ID', type: 'integer', format: 'int64'),
        new OA\Property(property: 'region_id', description: 'The region up to which the order is valid', type: 'integer'),
        new OA\Property(property: 'location_id', description: 'The structure where the order is', type: 'integer', format: 'int64'),
        new OA\Property(property: 'range', description: 'The range the order is covering', type: 'integer'),
        new OA\Property(property: 'is_buy_order', description: 'True if the order is a buy order', type: 'boolean'),
        new OA\Property(property: 'price', description: 'The unit price', type: 'number', format: 'double'),
        new OA\Property(property: 'volume_total', description: 'The order initial volume', type: 'number', format: 'double'),
        new OA\Property(property: 'volume_remain', description: 'The order remaining volume', type: 'number', format: 'double'),
        new OA\Property(property: 'issued', description: 'The date/time when the order has been created', type: 'string', format: 'date-time'),
        new OA\Property(property: 'issued_buy', description: 'The entity ID who creates the order', type: 'integer', format: 'int64'),
        new OA\Property(property: 'min_volume', description: 'The minimum volume which is requested for a buy order', type: 'number', format: 'double'),
        new OA\Property(property: 'wallet_division', description: 'The division to which the order is depending', type: 'integer'),
        new OA\Property(property: 'duration', description: 'The number of seconds the order is valid', type: 'integer'),
        new OA\Property(property: 'escrow', type: 'number', format: 'double'),
        new OA\Property(property: 'type', ref: '#/components/schemas/InvType', description: 'The type to which order is referring'),
    ],
    type: 'object'
)]
class CorporationOrder extends Model
{
    /**
     * @var array
     */
    protected $hidden = ['id', 'corporation_id', 'type_id', 'created_at', 'updated_at'];

    /**
     * @var array
     */
    protected $casts = [
        'is_buy_order' => 'boolean',
    ];

    /**
     * @var bool
     */
    protected static $unguarded = true;

    /**
     * @param  $value
     */
    public function setIssuedAttribute($value)
    {
        $this->attributes['issued'] = is_null($value) ? null : carbon($value);
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasOne
     */
    public function location()
    {
        return $this->hasOne(UniverseStructure::class, 'structure_id', 'location_id')
            ->withDefault([
                'name' => trans('web::seat.unknown'),
            ]);
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasOne
     */
    public function type()
    {
        return $this->hasOne(InvType::class, 'typeID', 'type_id')
            ->withDefault([
                'typeName' => trans('web::seat.unknown'),
            ]);
    }
}
