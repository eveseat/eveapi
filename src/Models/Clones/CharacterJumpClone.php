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

namespace Seat\Eveapi\Models\Clones;

use Illuminate\Database\Eloquent\Model;
use Seat\Eveapi\Models\Sde\StaStation;
use Seat\Eveapi\Models\Universe\UniverseStructure;

/**
 * Class CharacterJumpClone.
 *
 * @package Seat\Eveapi\Models\Clones
 *
 * @OA\Schema(
 *     description="Character Jump Clone",
 *     title="CharacterJumpClone",
 *     type="object"
 * )
 *
 * @OA\Property(
 *     type="integer",
 *     format="int64",
 *     property="jump_clone_id",
 *     description="Unique jump clone identifier"
 * )
 *
 * @OA\Property(
 *     type="string",
 *     property="name",
 *     description="Clone name if set"
 * )
 *
 * @OA\Property(
 *     type="integer",
 *     format="int64",
 *     property="location_id",
 *     description="The structure into which the clone resides"
 * )
 *
 * @OA\Property(
 *     type="string",
 *     enum={"station","structure"},
 *     property="location_type",
 *     description="The structure type qualifier"
 * )
 *
 * @OA\Property(
 *     type="array",
 *     property="implants",
 *     description="A list of type ID",
 *     @OA\Items(type="integer")
 * )
 */
class CharacterJumpClone extends Model
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
