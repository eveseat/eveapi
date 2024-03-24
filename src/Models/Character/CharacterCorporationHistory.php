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

namespace Seat\Eveapi\Models\Character;

use OpenApi\Attributes as OA;
use Seat\Eveapi\Models\Universe\UniverseName;
use Seat\Services\Models\ExtensibleModel;

#[OA\Schema(
    title: 'CharacterCorporationHistory',
    description: 'Character Corporation History',
    properties: [
        new OA\Property(property: 'start_date', description: 'The date/time from which the character was inside the corporation', type: 'string', format: 'date-time'),
        new OA\Property(property: 'corporation_id', description: 'The corporation ID into which the character was', type: 'integer', format: 'int64'),
        new OA\Property(property: 'is_deleted', description: 'True if the corporation has been close', type: 'boolean'),
        new OA\Property(property: 'record_id', description: 'Sorting key', type: 'integer'),
    ],
    type: 'object'
)]
class CharacterCorporationHistory extends ExtensibleModel
{
    /**
     * @var array
     */
    protected $hidden = ['id', 'created_at', 'updated_at'];

    /**
     * @var array
     */
    protected $casts = [
        'is_deleted' => 'boolean',
    ];

    /**
     * @var bool
     */
    protected static $unguarded = true;

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasOne
     */
    public function corporation()
    {
        return $this->hasOne(UniverseName::class, 'entity_id', 'corporation_id')
            ->withDefault([
                'category' => 'corporation',
                'name' => trans('web::seat.unknown'),
            ]);
    }
}
