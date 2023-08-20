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

namespace Seat\Eveapi\Models\Corporation;

use Illuminate\Database\Eloquent\Model;
use OpenApi\Attributes as OA;
use Seat\Eveapi\Models\RefreshToken;
use Seat\Eveapi\Models\Sde\InvType;
use Seat\Eveapi\Models\Sde\SolarSystem;
use Seat\Eveapi\Models\Sde\StaStation;
use Seat\Eveapi\Models\Universe\UniverseName;
use Seat\Eveapi\Models\Universe\UniverseStructure;

#[OA\Schema(
    title: 'CorporationMemberTracking',
    description: 'Corporation Member Tracking',
    properties: [
        new OA\Property(property: 'character_id', description: 'The character ID', type: 'integer', format: 'int64'),
        new OA\Property(property: 'start_date', description: 'The date since which the character is member of the corporation', type: 'string', format: 'date-time'),
        new OA\Property(property: 'base_id', description: 'The structure to which the main location of this character is set', type: 'integer', format: 'int64'),
        new OA\Property(property: 'logoff_date', description: 'The last time when the character signed out', type: 'string', format: 'date-time'),
        new OA\Property(property: 'location_id', description: 'The place where the character is', type: 'integer', format: 'int64'),
        new OA\Property(property: 'ship', ref: '#/components/schemas/InvType', description: 'The ship information'),
    ],
    type: 'object'
)]
class CorporationMemberTracking extends Model
{
    /**
     * @var bool
     */
    protected static $unguarded = true;

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function ship()
    {

        return $this->belongsTo(InvType::class, 'ship_type_id', 'typeID')
            ->withDefault();
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     *
     * @deprecated
     */
    public function refresh_token()
    {

        return $this->belongsTo(RefreshToken::class, 'character_id', 'character_id');
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function location()
    {
        // System range
        if ($this->location_id >= 30000001 && $this->location_id <= 31002604)
            return $this->belongsTo(SolarSystem::class, 'location_id', 'system_id');

        // Station range
        if ($this->location_id >= 60000000 && $this->location_id <= 64000000)
            return $this->belongsTo(StaStation::class, 'location_id', 'stationID');

        return $this->belongsTo(UniverseStructure::class, 'location_id', 'structure_id');
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function roles()
    {
        return $this->hasMany(CorporationRole::class, 'character_id', 'character_id');
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasOne
     */
    public function character()
    {
        return $this->hasOne(UniverseName::class, 'entity_id', 'character_id')
            ->withDefault([
                'name'      => trans('web::seat.unknown'),
                'category'  => 'character',
            ]);
    }
}
