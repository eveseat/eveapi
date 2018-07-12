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

namespace Seat\Eveapi\Models\Contracts;

use Illuminate\Database\Eloquent\Model;

/**
 * Class ContractDetail.
 * @package Seat\Eveapi\Models\Contacts
 *
 * @SWG\Definition(
 *     description="Contract Detail",
 *     title="ContractDetail",
 *     type="object"
 * )
 *
 * @SWG\Property(
 *     type="integer",
 *     format="int64",
 *     property="contract_id",
 *     description="The contract identifier"
 * )
 *
 * @SWG\Property(
 *     type="integer",
 *     format="int64",
 *     property="issuer_id",
 *     description="The entity ID who created the contract"
 * )
 *
 * @SWG\Property(
 *     type="integer",
 *     format="int64",
 *     property="issuer_corporation_id",
 *     description="The corporation ID from the contract creator"
 * )
 *
 * @SWG\Property(
 *     type="integer",
 *     format="int64",
 *     property="assignee_id",
 *     description="The entity ID to whom the created has been created"
 * )
 *
 * @SWG\Property(
 *     type="integer",
 *     format="int64",
 *     property="acceptor_id",
 *     description="The entity ID who accept the contract"
 * )
 *
 * @SWG\Property(
 *     type="integer",
 *     format="int64",
 *     property="start_location_id",
 *     description="The structure from where the contract has been made"
 * )
 *
 * @SWG\Property(
 *     type="integer",
 *     format="int64",
 *     property="end_location_id",
 *     description="The structure to where the contract should be deliver"
 * )
 *
 * @SWG\Property(
 *     type="string",
 *     enum={"unknown","item_exchange","auction","courier","loan"},
 *     property="type",
 *     description="The contract type"
 * )
 *
 * @SWG\Property(
 *     type="string",
 *     enum={"outstanding","in_progress","finished_issuer","finished_contractor","finished","cancelled","rejected","failed","deleted","reversed"},
 *     property="status",
 *     description="The contract status"
 * )
 *
 * @SWG\Property(
 *     type="string",
 *     property="title",
 *     description="The contract description"
 * )
 *
 * @SWG\Property(
 *     type="boolean",
 *     property="for_corporation",
 *     description="True if the contract is a corporation contract"
 * )
 *
 * @SWG\Property(
 *     type="string",
 *     enum={"public","personal","corporation","alliance"},
 *     property="availability",
 *     description="The contract availability scope"
 * )
 *
 * @SWG\Property(
 *     type="string",
 *     format="date-time",
 *     property="date_issued",
 *     description="The date-time when the contract has been made"
 * )
 *
 * @SWG\Property(
 *     type="string",
 *     format="date-time",
 *     property="date_expired",
 *     description="The date-time when the contract is expiring"
 * )
 *
 * @SWG\Property(
 *     type="string",
 *     format="date-time",
 *     property="date_accepted",
 *     description="The date-time when the contract has been accepted"
 * )
 *
 * @SWG\Property(
 *     type="integer",
 *     property="days_to_complete",
 *     description="The amount of day during which the contract is going (for courier contract)"
 * )
 *
 * @SWG\Property(
 *     type="string",
 *     format="date-time",
 *     property="date_completed",
 *     description="The date-time when the contract has been completed"
 * )
 *
 * @SWG\Property(
 *     type="number",
 *     format="double",
 *     property="price",
 *     description="The amount of ISK the acceptor entity must pay to get the contract"
 * )
 *
 * @SWG\Property(
 *     type="number",
 *     format="double",
 *     property="reward",
 *     description="The amount of ISK the acceptor entity is earning by accepting the contract"
 * )
 *
 * @SWG\Property(
 *     type="number",
 *     format="double",
 *     property="collateral",
 *     description="The amount of ISK the acceptor entity have to pay in case of failure"
 * )
 *
 * @SWG\Property(
 *     type="number",
 *     format="double",
 *     property="buyout",
 *     description="The amount of ISK the contract is completed (for auction)"
 * )
 *
 * @SWG\Property(
 *     type="number",
 *     format="double",
 *     property="volume",
 *     description="The contract volume"
 * )
 *
 * @SWG\Property(
 *     type="string",
 *     format="date-time",
 *     property="created_at",
 *     description="The date-time when the record has been created in SeAT"
 * )
 *
 * @SWG\Property(
 *     type="string",
 *     format="date-time",
 *     property="updated_at",
 *     description="The date-time when the record has been updated in SeAT"
 * )
 */
class ContractDetail extends Model
{
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
}
