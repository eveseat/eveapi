<?php

/*
 * This file is part of SeAT
 *
 * Copyright (C) 2015, 2016, 2017, 2018, 2019  Leon Jacobs
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

namespace Seat\Eveapi\Models\Contacts;

use Illuminate\Database\Eloquent\Model;
use Seat\Eveapi\Models\Universe\UniverseName;

/**
 * Class CharacterContact.
 * @package Seat\Eveapi\Models\Contacts
 *
 * @OA\Schema(
 *     description="Character Contact",
 *     title="CharacterContact",
 *     type="object"
 * )
 *
 * @OA\Property(
 *     type="integer",
 *     format="int64",
 *     property="contact_id",
 *     description="The entity ID"
 * )
 *
 * @OA\Property(
 *     type="number",
 *     format="float",
 *     property="standing",
 *     description="The standing between -10 and 10"
 * )
 *
 * @OA\Property(
 *     type="string",
 *     enum={"character","corporation","alliance","faction"},
 *     property="contact_type",
 *     description="The entity type"
 * )
 *
 * @OA\Property(
 *     type="boolean",
 *     property="is_watched",
 *     description="True if the contact is in the watchlist"
 * )
 *
 * @OA\Property(
 *     type="boolean",
 *     property="is_blocked",
 *     description="True if the contact is in the blacklist"
 * )
 *
 * @OA\Property(
 *     property="labels",
 *     type="array",
 *     description="Labels attached to the the contact",
 *     @OA\Items(type="string")
 * )
 */
class CharacterContact extends Model
{

    /**
     * @var array
     */
    protected $casts = [
        'is_watched' => 'boolean',
        'is_blocked' => 'boolean',
        'label_ids' => 'array',
    ];

    /**
     * @var array
     */
    protected $hidden = [
        'created_at', 'updated_at',
    ];

    /**
     * @var bool
     */
    protected static $unguarded = true;

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasOne
     */
    public function entity()
    {
        return $this->hasOne(UniverseName::class, 'entity_id', 'contact_id')
            ->withDefault([
                'name'      => trans('web::seat.unknown'),
                'category'  => $this->contact_type,
            ]);
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
     */
    public function labels()
    {
        return $this->belongsToMany(CharacterLabel::class);
    }
}
