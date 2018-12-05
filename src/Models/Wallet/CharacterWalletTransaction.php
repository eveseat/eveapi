<?php

/*
 * This file is part of SeAT
 *
 * Copyright (C) 2015, 2016, 2017, 2018  Leon Jacobs
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
use Seat\Eveapi\Traits\HasCompositePrimaryKey;

/**
 * Class CharacterWalletTransaction.
 * @package Seat\Eveapi\Models\Wallet
 *
 * @SWG\Definition(
 *     description="Corporation Wallet Transaction",
 *     title="CorporationWalletTransaction",
 *     type="object"
 * )
 *
 * @SWG\Property(
 *     type="integer",
 *     format="int64",
 *     property="transaction_id",
 *     description="Unique transaction ID"
 * )
 *
 * @SWG\Property(
 *     type="string",
 *     format="date-time",
 *     property="date",
 *     description="Date and time of transaction"
 * )
 *
 * @SWG\Property(
 *     type="integer",
 *     format="int64",
 *     property="location_id",
 *     description="The place where the transaction has been made"
 * )
 *
 * @SWG\Property(
 *     type="number",
 *     format="double",
 *     property="unit_price",
 *     description="Amount paid per unit"
 * )
 *
 * @SWG\Property(
 *     type="integer",
 *     property="quantity"
 * )
 *
 * @SWG\Property(
 *     type="integer",
 *     format="int64",
 *     property="client_id"
 * )
 *
 * @SWG\Property(
 *     type="boolean",
 *     property="is_buy",
 *     description="True if the transaction is related to a buy order"
 * )
 *
 * @SWG\Property(
 *     type="boolean",
 *     property="is_personal",
 *     description="True if the transaction is not related to the corporation"
 * )
 *
 * @SWG\Property(
 *     type="integer",
 *     format="int64",
 *     property="journal_ref_id",
 *     description="-1 if there is no corresponding wallet journal entry"
 * )
 *
 * @SWG\Property(
 *     type="string",
 *     format="date-time",
 *     property="created_at",
 *     description="The date-time when transaction has been created into SeAT"
 * )
 *
 * @SWG\Property(
 *     type="string",
 *     format="date-time",
 *     property="updated_at",
 *     description="The date-time when transaction has been updated into SeAT"
 * )
 *
 * @SWG\Property(
 *     property="type",
 *     ref="#/definitions/InvType"
 * )
 */
class CharacterWalletTransaction extends Model
{
    use HasCompositePrimaryKey;

    /**
     * @var bool
     */
    protected static $unguarded = true;

    /**
     * @var bool
     */
    public $incrementing = false;

    /**
     * @var array
     */
    protected $primaryKey = ['character_id', 'transaction_id'];

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasOne
     */
    public function type()
    {

        return $this->hasOne(InvType::class, 'typeID', 'type_id');
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasOne
     */
    public function client()
    {

        return $this->hasOne(UniverseName::class, 'entity_id','client_id');
    }


}
