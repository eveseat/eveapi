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

namespace Seat\Eveapi\Models\Contacts;

use Illuminate\Database\Eloquent\Model;
use OpenApi\Attributes as OA;
use Seat\Eveapi\Models\Universe\UniverseName;

#[OA\Schema(
    title: 'CorporationContact',
    description: 'Corporation Contact',
    properties: [
        new OA\Property(property: 'contact_id', description: 'The entity ID', type: 'integer', format: 'int64'),
        new OA\Property(property: 'standing', description: 'The standing between -10 and 10', type: 'number', format: 'float'),
        new OA\Property(property: 'contact_type', description: 'The entity type', type: 'string', enum: ['character', 'corporation', 'alliance', 'faction']),
        new OA\Property(property: 'is_watched', description: 'True if the contact is in the watchlist', type: 'boolean'),
        new OA\Property(property: 'is_blocked', description: 'True if the contact is in the blacklist', type: 'boolean'),
        new OA\Property(property: 'labels', description: 'Labels attached to the contact', type: 'array', items: new OA\Items(type: 'string'))
    ],
    type: 'object'
)]
class CorporationContact extends Model
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
        return $this->belongsToMany(CorporationLabel::class);
    }
}
