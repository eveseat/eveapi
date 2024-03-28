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

namespace Seat\Eveapi\Models\Universe;

use OpenApi\Attributes as OA;
use Seat\Eveapi\Models\Character\CharacterAffiliation;
use Seat\Services\Models\ExtensibleModel;

#[OA\Schema(
    title: 'UniverseName',
    description: 'Universe Name',
    properties: [
        new OA\Property(property: 'entity_id', description: 'The entity identifier', type: 'integer', format: 'int64'),
        new OA\Property(property: 'name', description: 'The entity name', type: 'string'),
        new OA\Property(property: 'category', description: 'The entity type', type: 'string', enum: ['alliance', 'character', 'constellation', 'corporation', 'inventory_type', 'region', 'solar_system', 'station', 'faction']),
    ],
    type: 'object'
)]
class UniverseName extends ExtensibleModel
{
    /**
     * @var array
     */
    protected $hidden = ['created_at', 'updated_at'];

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
    protected $primaryKey = 'entity_id';

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasOne
     */
    public function affiliation()
    {
        return $this->hasOne(CharacterAffiliation::class, 'character_id', 'entity_id')
            ->withDefault();
    }
}
