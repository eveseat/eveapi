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

namespace Seat\Eveapi\Models\Clones;

use OpenApi\Attributes as OA;
use Seat\Eveapi\Models\Sde\StaStation;
use Seat\Eveapi\Models\Universe\UniverseStructure;
use Seat\Services\Models\ExtensibleModel;

#[OA\Schema(
    title: 'CharacterJumpClone',
    description: 'Character Jump Clone',
    properties: [
        new OA\Property(property: 'jump_clone_id', description: 'Unique jump clone identifier', type: 'integer', format: 'int64'),
        new OA\Property(property: 'name', description: 'Clone name if set', type: 'string'),
        new OA\Property(property: 'location_id', description: 'The structure into which the clone resides', type: 'integer', format: 'int64'),
        new OA\Property(property: 'location_type', description: 'The structure type qualifier', type: 'string', enum: ['station', 'structure']),
        new OA\Property(property: 'implants', description: 'A list of type ID', type: 'array', items: new OA\Items(type: 'integer')),
    ],
    type: 'object'
)]
class CharacterJumpClone extends ExtensibleModel
{
    /**
     * @var array
     */
    protected $hidden = ['id', 'created_at', 'updated_at'];

    /**
     * @var bool
     */
    protected static $unguarded = true;

    /**
     * @var array
     */
    protected $casts = [
        'implants' => 'array',
    ];

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function location()
    {

        if ($this->location_type == 'station')
            return $this->belongsTo(StaStation::class, 'location_id', 'stationID');

        return $this->belongsTo(UniverseStructure::class, 'location_id', 'structure_id');
    }
}
