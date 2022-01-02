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

namespace Seat\Eveapi\Models\Contracts;

use Illuminate\Database\Eloquent\Model;
use Seat\Eveapi\Models\Sde\InvType;

/**
 * Class ContractItem.
 *
 * @package Seat\Eveapi\Models\Contacts
 *
 * @OA\Schema(
 *     description="Contract Item",
 *     title="ContractItem",
 *     type="object"
 * )
 *
 * @OA\Property(
 *     property="type_id",
 *     type="integer",
 *     description="The item type identifier"
 * )
 *
 * @OA\Property(
 *     property="quantity",
 *     type="number",
 *     description="The item quantity"
 * )
 *
 * @OA\Property(
 *     property="raw_quantity",
 *     type="integer",
 *     minimum=-2
 * )
 *
 * @OA\Property(
 *     property="is_singleton",
 *     type="boolean",
 *     description="Determine if the item is stacked"
 * )
 *
 * @OA\Property(
 *     property="is_included",
 *     type="boolean",
 *     description="Determine if the item is contained in a parent item"
 * )
 */
class ContractItem extends Model
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
}
