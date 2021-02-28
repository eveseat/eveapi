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

namespace Seat\Eveapi\Models\Wallet;

use Illuminate\Database\Eloquent\Model;
use Seat\Eveapi\Models\Sde\InvType;
use Seat\Eveapi\Models\Universe\UniverseName;
use Seat\Eveapi\Models\Universe\UniverseStructure;

/**
 * Class CharacterWalletTransaction.
 * @package Seat\Eveapi\Models\Wallet
 *
 * @OA\Schema(
 *     description="Corporation Wallet Transaction",
 *     title="CorporationWalletTransaction",
 *     type="object"
 * )
 *
 * @OA\Property(
 *     type="integer",
 *     format="int64",
 *     property="transaction_id",
 *     description="Unique transaction ID"
 * )
 *
 * @OA\Property(
 *     type="string",
 *     format="date-time",
 *     property="date",
 *     description="Date and time of transaction"
 * )
 *
 * @OA\Property(
 *     type="integer",
 *     format="int64",
 *     property="location_id",
 *     description="The place where the transaction has been made"
 * )
 *
 * @OA\Property(
 *     type="number",
 *     format="double",
 *     property="unit_price",
 *     description="Amount paid per unit"
 * )
 *
 * @OA\Property(
 *     type="integer",
 *     property="quantity"
 * )
 *
 * @OA\Property(
 *     type="boolean",
 *     property="is_buy",
 *     description="True if the transaction is related to a buy order"
 * )
 *
 * @OA\Property(
 *     type="boolean",
 *     property="is_personal",
 *     description="True if the transaction is not related to the corporation"
 * )
 *
 * @OA\Property(
 *     type="integer",
 *     format="int64",
 *     property="journal_ref_id",
 *     description="-1 if there is no corresponding wallet journal entry"
 * )
 *
 * @OA\Property(
 *     property="party",
 *     ref="#/components/schemas/UniverseName"
 * )
 *
 * @OA\Property(
 *     property="type",
 *     ref="#/components/schemas/InvType"
 * )
 */
class CharacterWalletTransaction extends Model
{
    /**
     * @var array
     */
    protected $casts = [
        'is_buy' => 'boolean',
        'is_personal' => 'boolean',
    ];

    /**
     * @var array
     */
    protected $hidden = ['character_id', 'client_id', 'type_id', 'created_at', 'updated_at'];

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
     * @param $value
     */
    public function setDateAttribute($value)
    {
        $this->attributes['date'] = is_null($value) ? null : carbon($value);
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

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasOne
     */
    public function party()
    {

        return $this->hasOne(UniverseName::class, 'entity_id', 'client_id')
            ->withDefault([
                'name'      => trans('web::seat.unknown'),
                'category'  => 'character',
            ]);
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
}
