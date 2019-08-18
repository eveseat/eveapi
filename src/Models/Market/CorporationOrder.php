<?php

/*
 * This file is part of SeAT
 *
 * Copyright (C) 2015, 2016, 2017, 2018, 2019  Leon Jacobs
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
use Seat\Eveapi\Models\Sde\InvType;
use Seat\Eveapi\Models\Universe\UniverseStructure;
use Seat\Eveapi\Traits\HasCompositePrimaryKey;

/**
 * Class CorporationOrder.
 * @package Seat\Eveapi\Models\Market
 *
 * @SWG\Definition(
 *     description="Corporation Order",
 *     title="CorporationOrder",
 *     type="object"
 * )
 *
 * @SWG\Property(
 *     type="integer",
 *     format="int64",
 *     property="order_id",
 *     description="The market order ID"
 * )
 *
 * @SWG\Property(
 *     type="integer",
 *     property="type_id",
 *     description="The type to which order is referring"
 * )
 *
 * @SWG\Property(
 *     type="integer",
 *     property="region_id",
 *     description="The region up to which the order is valid"
 * )
 *
 * @SWG\Property(
 *     type="integer",
 *     format="int64",
 *     property="location_id",
 *     description="The structure where the order is"
 * )
 *
 * @SWG\Property(
 *     type="integer",
 *     property="range",
 *     description="The range the order is covering"
 * )
 *
 * @SWG\Property(
 *     type="boolean",
 *     property="is_buy_order",
 *     description="True if the order is a buy order"
 * )
 *
 * @SWG\Property(
 *     type="number",
 *     format="double",
 *     property="price",
 *     description="The unit price"
 * )
 *
 * @SWG\Property(
 *     type="number",
 *     format="double",
 *     property="volume_total",
 *     description="The order initial volume"
 * )
 *
 * @SWG\Property(
 *     type="number",
 *     format="double",
 *     property="volume_remain",
 *     description="The order remaining volume"
 * )
 *
 * @SWG\Property(
 *     type="string",
 *     format="date-time",
 *     property="issued",
 *     description="The date-time when the order has been created"
 * )
 *
 * @SWG\Property(
 *     type="number",
 *     format="double",
 *     property="min_volume",
 *     description="The minimum volume which is requested for a buy order"
 * )
 *
 * @SWG\Property(
 *     type="integer",
 *     property="duration",
 *     description="The number of seconds the order is valid"
 * )
 *
 * @SWG\Property(
 *     type="number",
 *     format="double",
 *     property="escrow"
 * )
 *
 * @SWG\Property(
 *     type="integer",
 *     minimum=1,
 *     property="wallet_division",
 *     description="The division to which the order is depending."
 * )
 *
 * @SWG\Property(
 *     type="string",
 *     format="date-time",
 *     property="created_at",
 *     description="The date-time when order has been created into SeAT"
 * )
 *
 * @SWG\Property(
 *     type="string",
 *     format="date-time",
 *     property="updated_at",
 *     description="The date-time when order has been updated into SeAT"
 * )
 */
class CorporationOrder extends Model
{
    use HasCompositePrimaryKey;

    /**
     * @var bool
     */
    protected static $unguarded = true;

    /**
     * @var array
     */
    protected $primaryKey = ['corporation_id', 'order_id'];

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
                'typeID'   => 0,
                'typeName' => trans('web::seat.unknown'),
            ]);
    }
}
