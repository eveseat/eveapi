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

namespace Seat\Eveapi\Models\Universe;

use Illuminate\Database\Eloquent\Model;
use OpenApi\Annotations as OA;
use Seat\Eveapi\Models\Character\CharacterAffiliation;

/**
 * Class UniverseName.
 *
 * @package Seat\Eveapi\Models\Universe
 *
 * @OA\Schema(
 *      description="Universe Name",
 *      title="UniverseName",
 *      type="object"
 * )
 *
 * @OA\Property(
 *      property="entity_id",
 *      type="integer",
 *      format="int64",
 *      description="The entity identifier"
 * )
 *
 * @OA\Property(
 *      property="name",
 *      type="string",
 *      description="The entity name"
 * )
 *
 * @OA\Property(
 *      property="category",
 *      type="string",
 *      enum={"alliance","character","constellation","corporation","inventory_type","region","solar_system","station","faction"},
 *      description="The entity type"
 * )
 */
class UniverseName extends Model
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
