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

namespace Seat\Eveapi\Models\Contracts;

use OpenApi\Attributes as OA;
use Seat\Eveapi\Models\Sde\InvType;
use Seat\Services\Contracts\HasTypeID;
use Seat\Services\Models\ExtensibleModel;

#[OA\Schema(
    title: 'ContractItem',
    description: 'Contract Item',
    properties: [
        new OA\Property(property: 'type_id', description: 'The item type identifier', type: 'integer'),
        new OA\Property(property: 'quantity', description: 'The item quantity', type: 'integer'),
        new OA\Property(property: 'raw_quantity', type: 'integer', minimum: 2),
        new OA\Property(property: 'is_singleton', description: 'Determine if the item is stacked', type: 'boolean'),
        new OA\Property(property: 'is_included', description: 'Determine if the item is contained in a parent item', type: 'boolean'),
    ],
    type: 'object'
)]
class ContractItem extends ExtensibleModel implements HasTypeID
{
    /**
     * @var array
     */
    protected $casts = [
        'is_singleton' => 'boolean',
        'is_included' => 'boolean',
    ];

    /**
     * @var array
     */
    protected $hidden = ['contract_id', 'record_id', 'created_at', 'updated_at'];

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
    protected $primaryKey = 'record_id';

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function contract()
    {
        return $this->belongsTo(ContractDetail::class, 'contract_id', 'contract_id');
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
     * @return int The eve type id of this object
     */
    public function getTypeID(): int
    {
        return $this->type_id;
    }
}
