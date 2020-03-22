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

namespace Seat\Eveapi\Models\Contracts;

use Illuminate\Database\Eloquent\Model;

/**
 * Class ContractBid.
 * @package Seat\Eveapi\Models\Contacts
 *
 * @OA\Schema(
 *     description="Contract Bid",
 *     title="ContractBid",
 *     type="object"
 * )
 *
 * @OA\Property(
 *     property="bidder_id",
 *     type="integer",
 *     format="int64",
 *     description="Identifier from entity who put a bid"
 * )
 *
 * @OA\Property(
 *     property="date_bid",
 *     type="string",
 *     format="date-time",
 *     description="Date/Time when the bid has been placed"
 * )
 *
 * @OA\Property(
 *     property="amount",
 *     type="number",
 *     description="Amount of placed bid"
 * )
 *
 */
class ContractBid extends Model
{
    /**
     * @var array
     */
    protected $hidden = ['bid_id', 'contract_id', 'created_at', 'updated_at'];

    /**
     * @var bool
     */
    protected static $unguarded = true;

    /**
     * @var bool
     */
    public $incrementing = false;

    /**
     * @var string
     */
    protected $primaryKey = 'bid_id';
}
