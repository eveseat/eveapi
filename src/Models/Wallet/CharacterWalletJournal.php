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

namespace Seat\Eveapi\Models\Wallet;

use Illuminate\Database\Eloquent\Model;
use OpenApi\Attributes as OA;
use Seat\Eveapi\Models\Character\CharacterInfo;
use Seat\Eveapi\Models\Universe\UniverseName;

#[OA\Schema(
    title: 'CharacterWalletJournal',
    description: 'Character Wallet Journal',
    properties: [
        new OA\Property(property: 'id', description: 'Unique journal reference ID', type: 'integer', format: 'int64'),
        new OA\Property(property: 'date', description: 'The transaction date/time', type: 'string', format: 'date-time'),
        new OA\Property(property: 'ref_type', description: 'The type for the given transaction. Different transaction types will populate different attributes. Note: If you have an existing XML API application that is using ref_types, you will need to know which string ESI ref_type maps to which integer. You can look at the following file to see string->int mappings: https://github.com/ccpgames/eve-glue/blob/master/eve_glue/wallet_journal_ref.py', type: 'string'),
        new OA\Property(property: 'amount', description: 'The amount of ISK given or taken from the wallet as a result of the given transaction. Positive when ISK is deposited into the wallet and negative when ISK is withdrawn', type: 'number', format: 'float'),
        new OA\Property(property: 'balance', description: 'Wallet balance after transaction occurred', type: 'number', format: 'double'),
        new OA\Property(property: 'reason', description: 'The user stated reason for the transaction. Only applies to some ref_types', type: 'string'),
        new OA\Property(property: 'tax_receiver_id', description: 'The corporation ID receiving any tax paid. Only applies to tax related transactions', type: 'integer', format: 'int64'),
        new OA\Property(property: 'tax', description: 'Tax amount received. Only applies to tax related transactions', type: 'number', format: 'double'),
        new OA\Property(property: 'context_id', description: 'An ID that gives extra context to the particular transaction. Because of legacy reasons the context is completely different per ref_type and means different things. It is also possible to not have a context_id', type: 'integer', format: 'int64'),
        new OA\Property(property: 'context_id_type', description: 'The type of the given context_id if present', type: 'string', enum: ['structure_id', 'station_id', 'market_transaction_id', 'character_id', 'corporation_id', 'alliance_id', 'eve_system', 'industry_job_id', 'contract_id', 'planet_id', 'system_id', 'type_id']),
        new OA\Property(property: 'description', description: 'The reason for the transaction, mirrors what is seen in the client', type: 'string'),
        new OA\Property(property: 'first_party', ref: '#/components/schemas/UniverseName', description: 'The id of the first party involved in the transaction. This attribute has no consistency and is different or non existant for particular ref_types. The description attribute will help make sense of what this attribute means. For more info about the given ID it can be dropped into the /universe/names/ ESI route to determine its type and name'),
        new OA\Property(property: 'second_party', ref: '#/components/schemas/UniverseName', description: 'The id of the second party involved in the transaction. This attribute has no consistency and is different or non existant for particular ref_types. The description attribute will help make sense of what this attribute means. For more info about the given ID it can be dropped into the /universe/names/ ESI route to determine its type and name'),
    ],
    type: 'object'
)]
class CharacterWalletJournal extends Model
{
    /**
     * @var array
     */
    protected $hidden = ['character_id', 'first_party_id', 'second_party_id', 'created_at', 'updated_at'];

    /**
     * @var bool
     */
    protected static $unguarded = true;

    /**
     * @var bool
     */
    public $incrementing = false;

    /**
     * @param  $value
     */
    public function setDateAttribute($value)
    {
        $this->attributes['date'] = is_null($value) ? null : carbon($value);
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function character()
    {
        return $this->belongsTo(CharacterInfo::class, 'character_id', 'character_id')
            ->withDefault([
                'corporation_id' => 0,
                'alliance_id'    => 0,
                'faction_id'     => 0,
            ]);
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
