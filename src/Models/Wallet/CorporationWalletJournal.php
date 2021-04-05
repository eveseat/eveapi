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

namespace Seat\Eveapi\Models\Wallet;

use Illuminate\Database\Eloquent\Model;
use Seat\Eveapi\Models\Universe\UniverseName;
use Seat\Eveapi\Traits\CanUpsertIgnoreReplace;

/**
 * Class CorporationWalletJournal.
 * @package Seat\Eveapi\Models\Wallet
 *
 * @OA\Schema(
 *     description="Corporation Wallet Journal",
 *     title="CorporationWalletJournal",
 *     type="object"
 * )
 *
 * @OA\Property(
 *     type="integer",
 *     format="int64",
 *     property="id",
 *     description="Unique journal reference ID"
 * )
 *
 * @OA\Property(
 *     type="integer",
 *     property="division",
 *     description="Wallet key of the division to fetch journals from"
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
 *     type="string",
 *     property="ref_type",
 *     description="The transaction type for the given transaction. Different transaction types will populate different attributes. Note: If you have an existing XML API application that is using ref_types, you will need to know which string ESI ref_type maps to which integer. You can look at the following file to see string->int mappings: https://github.com/ccpgames/eve-glue/blob/master/eve_glue/wallet_journal_ref.py"
 * )
 *
 * @OA\Property(
 *     type="number",
 *     format="double",
 *     property="amount",
 *     description="The amount of ISK given or taken from the wallet as a result of the given transaction. Positive when ISK is deposited into the wallet and negative when ISK is withdrawn"
 * )
 *
 * @OA\Property(
 *     type="number",
 *     format="double",
 *     property="balance",
 *     description="Wallet balance after transaction occurred"
 * )
 *
 * @OA\Property(
 *     type="string",
 *     property="reason",
 *     description="The user stated reason for the transaction. Only applies to some ref_types"
 * )
 *
 * @OA\Property(
 *     type="integer",
 *     format="int64",
 *     property="tax_receiver_id",
 *     description="The corporation ID receiving any tax paid. Only applies to tax related transactions"
 * )
 *
 * @OA\Property(
 *     type="number",
 *     format="double",
 *     property="tax",
 *     description="Tax amount received. Only applies to tax related transactions"
 * )
 *
 * @OA\Property(
 *     type="integer",
 *     format="int64",
 *     property="context_id",
 *     description="An ID that gives extra context to the particular transaction. Because of legacy reasons the context is completely different per ref_type and means different things. It is also possible to not have a context_id"
 * )
 *
 * @OA\Property(
 *     type="string",
 *     enum={"structure_id","station_id","market_transaction_id","character_id","corporation_id","alliance_id","eve_system","industry_job_id","contract_id","planet_id","system_id","type_id"},
 *     property="context_id_type",
 *     description="The type of the given context_id if present"
 * )
 *
 * @OA\Property(
 *     type="string",
 *     property="description",
 *     description="The reason for the transaction, mirrors what is seen in the client"
 * )
 *
 * @OA\Property(
 *     property="first_party",
 *     description="The id of the first party involved in the transaction. This attribute has no consistency and is different or non existant for particular ref_types. The description attribute will help make sense of what this attribute means. For more info about the given ID it can be dropped into the /universe/names/ ESI route to determine its type and name",
 *     ref="#/components/schemas/UniverseName"
 * )
 *
 * @OA\Property(
 *     property="second_party",
 *     description="The id of the second party involved in the transaction. This attribute has no consistency and is different or non existant for particular ref_types. The description attribute will help make sense of what this attribute means. For more info about the given ID it can be dropped into the /universe/names/ ESI route to determine its type and name",
 *     ref="#/components/schemas/UniverseName"
 * )
 */
class CorporationWalletJournal extends Model
{
    use CanUpsertIgnoreReplace;

    /**
     * @var array
     */
    protected $hidden = ['corporation_id', 'first_party_id', 'second_party_id', 'created_at', 'updated_at'];

    /**
     * @var bool
     */
    protected static $unguarded = true;

    /**
     * @var string
     */
    protected $primaryKey = 'internal_id';

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
    public function first_party()
    {

        return $this->hasOne(UniverseName::class, 'entity_id', 'first_party_id')
            ->withDefault([
                'name'      => trans('web::seat.unknown'),
                'category'  => 'character',
            ]);
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasOne
     */
    public function second_party()
    {

        return $this->hasOne(UniverseName::class, 'entity_id', 'second_party_id')
            ->withDefault([
                'name'      => trans('web::seat.unknown'),
                'category'  => 'character',
            ]);
    }
}
