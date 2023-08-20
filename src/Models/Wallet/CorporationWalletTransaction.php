<?php

/*
 * This file is part of SeAT
 *
 * Copyright (C) 2015 to present Leon Jacobs
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
use Seat\Eveapi\Models\Sde\InvType;
use Seat\Eveapi\Models\Universe\UniverseName;
use Seat\Eveapi\Models\Universe\UniverseStructure;

#[OA\Schema(
    title: 'CorporationWalletTransaction',
    description: 'Corporation Wallet Transaction',
    properties: [
        new OA\Property(property: 'division', description: 'Wallet key of the division to fetch journals from', type: 'integer'),
        new OA\Property(property: 'transaction_id', description: 'Unique transaction ID', type: 'integer', format: 'int64'),
        new OA\Property(property: 'date', description: 'The transaction date/time', type: 'string', format: 'date-time'),
        new OA\Property(property: 'location_id', description: 'The place where the transaction has been made', type: 'integer', format: 'int64'),
        new OA\Property(property: 'unit_price', description: 'Amount paid per unit', type: 'number', format: 'double'),
        new OA\Property(property: 'quantity', type: 'integer'),
        new OA\Property(property: 'is_buy', description: 'True if the transaction is related to a buy order', type: 'boolean'),
        new OA\Property(property: 'journal_ref_id', description: '-1 if there is no corresponding wallet journal entry', type: 'integer', format: 'int64'),
        new OA\Property(property: 'party', ref: '#/components/schemas/UniverseName'),
        new OA\Property(property: 'type', ref: '#/components/schemas/InvType'),
    ],
    type: 'object'
)]
class CorporationWalletTransaction extends Model
{
    /**
     * @var array
     */
    protected $casts = [
        'is_buy' => 'boolean',
    ];

    /**
     * @var array
     */
    protected $hidden = ['corporation_id', 'client_id', 'type_id', 'created_at', 'updated_at'];

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
     * @param  $value
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
