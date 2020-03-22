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

namespace Seat\Eveapi\Models\Contracts;

use Illuminate\Database\Eloquent\Model;
use Seat\Eveapi\Models\Universe\UniverseName;
use Seat\Eveapi\Models\Universe\UniverseStructure;

/**
 * Class ContractDetail.
 * @package Seat\Eveapi\Models\Contacts
 *
 * @OA\Schema(
 *     description="Contract Detail",
 *     title="ContractDetail",
 *     type="object"
 * )
 *
 * @OA\Property(
 *     type="integer",
 *     format="int64",
 *     property="contract_id",
 *     description="The contract identifier"
 * )
 *
 * @OA\Property(
 *     type="string",
 *     enum={"unknown","item_exchange","auction","courier","loan"},
 *     property="type",
 *     description="The contract type"
 * )
 *
 * @OA\Property(
 *     type="string",
 *     enum={"outstanding","in_progress","finished_issuer","finished_contractor","finished","cancelled","rejected","failed","deleted","reversed"},
 *     property="status",
 *     description="The contract status"
 * )
 *
 * @OA\Property(
 *     type="string",
 *     property="title",
 *     description="The contract description"
 * )
 *
 * @OA\Property(
 *     type="boolean",
 *     property="for_corporation",
 *     description="True if the contract is a corporation contract"
 * )
 *
 * @OA\Property(
 *     type="string",
 *     enum={"public","personal","corporation","alliance"},
 *     property="availability",
 *     description="The contract availability scope"
 * )
 *
 * @OA\Property(
 *     type="string",
 *     format="date-time",
 *     property="date_issued",
 *     description="The date-time when the contract has been made"
 * )
 *
 * @OA\Property(
 *     type="string",
 *     format="date-time",
 *     property="date_expired",
 *     description="The date-time when the contract is expiring"
 * )
 *
 * @OA\Property(
 *     type="string",
 *     format="date-time",
 *     property="date_accepted",
 *     description="The date-time when the contract has been accepted"
 * )
 *
 * @OA\Property(
 *     type="integer",
 *     property="days_to_complete",
 *     description="The amount of day during which the contract is going (for courier contract)"
 * )
 *
 * @OA\Property(
 *     type="string",
 *     format="date-time",
 *     property="date_completed",
 *     description="The date-time when the contract has been completed"
 * )
 *
 * @OA\Property(
 *     type="number",
 *     format="double",
 *     property="price",
 *     description="The amount of ISK the acceptor entity must pay to get the contract"
 * )
 *
 * @OA\Property(
 *     type="number",
 *     format="double",
 *     property="reward",
 *     description="The amount of ISK the acceptor entity is earning by accepting the contract"
 * )
 *
 * @OA\Property(
 *     type="number",
 *     format="double",
 *     property="collateral",
 *     description="The amount of ISK the acceptor entity have to pay in case of failure"
 * )
 *
 * @OA\Property(
 *     type="number",
 *     format="double",
 *     property="buyout",
 *     description="The amount of ISK the contract is completed (for auction)"
 * )
 *
 * @OA\Property(
 *     type="number",
 *     format="double",
 *     property="volume",
 *     description="The contract volume"
 * )
 *
 * @OA\Property(
 *     property="issuer",
 *     ref="#/components/schemas/UniverseName"
 * )
 *
 * @OA\Property(
 *     property="assignee",
 *     ref="#/components/schemas/UniverseName"
 * )
 *
 * @OA\Property(
 *     property="acceptor",
 *     ref="#/components/schemas/UniverseName"
 * )
 *
 * @OA\Property(
 *     property="bids",
 *     type="array",
 *     @OA\Items(ref="#/components/schemas/ContractBid")
 * )
 *
 * @OA\Property(
 *     property="lines",
 *     type="array",
 *     @OA\Items(ref="#/components/schemas/ContractItem")
 * )
 *
 * @OA\Property(
 *     property="start_location",
 *     ref="#/components/schemas/UniverseStructure"
 * )
 *
 * @OA\Property(
 *     property="end_location",
 *     ref="#/components/schemas/UniverseStructure"
 * )
 */
class ContractDetail extends Model
{
    /**
     * @var array
     */
    protected $casts = [
        'for_corporation' => 'boolean',
    ];
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
    protected $primaryKey = 'contract_id';

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasOne
     */
    public function acceptor()
    {
        return $this->hasOne(UniverseName::class, 'entity_id', 'acceptor_id')
            ->withDefault([
                'name'      => trans('web::seat.unknown'),
                'category'  => 'character',
            ]);
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasOne
     */
    public function assignee()
    {
        return $this->hasOne(UniverseName::class, 'entity_id', 'assignee_id')
            ->withDefault([
                'name'      => trans('web::seat.unknown'),
                'category'  => 'character',
            ]);
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasOne
     */
    public function issuer()
    {
        return $this->hasOne(UniverseName::class, 'entity_id', 'issuer_id')
            ->withDefault([
                'name'      => trans('web::seat.unknown'),
                'category'  => 'character',
            ]);
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function bids()
    {
        return $this->hasMany(ContractBid::class, 'contract_id');
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function lines()
    {
        return $this->hasMany(ContractItem::class, 'contract_id');
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasOne
     */
    public function start_location()
    {
        return $this->hasOne(UniverseStructure::class, 'structure_id', 'start_location_id')
            ->withDefault([
                'name' => trans('web::seat.unknown'),
            ]);
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasOne
     */
    public function end_location()
    {
        return $this->hasOne(UniverseStructure::class, 'structure_id', 'end_location_id')
            ->withDefault([
                'name' => trans('web::seat.unknown'),
            ]);
    }
}
