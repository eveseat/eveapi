<?php

/*
 * This file is part of SeAT
 *
 * Copyright (C) 2015 to 2020 Leon Jacobs
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
use Seat\Eveapi\Models\Sde\InvType;
use Seat\Eveapi\Models\Sde\MapDenormalize;
use Seat\Eveapi\Models\Sde\StaStation;
use Seat\Eveapi\Models\Universe\UniverseName;
use Seat\Eveapi\Models\Universe\UniverseStructure;
use Seat\Web\Models\User;

/**
 * Class CorporationMemberTracking.
 * @package Seat\Eveapi\Models\Corporation
 *
 * @SWG\Definition(
 *      description="Corporation Member Tracking",
 *      title="CorporationMemberTracking",
 *      type="object"
 * )
 *
 * @SWG\Property(
 *     type="integer",
 *     format="int64",
 *     property="character_id",
 *     description="The character ID"
 * )
 *
 * @SWG\Property(
 *     type="string",
 *     format="date-time",
 *     property="start_date",
 *     description="The date since which the character is member of the corporation"
 * )
 *
 * @SWG\Property(
 *     type="integer",
 *     format="int64",
 *     property="base_id",
 *     description="The structure to which the main location of this character is set"
 * )
 *
 * @SWG\Property(
 *     type="string",
 *     format="date-time",
 *     property="logon_date",
 *     description="The last time when we saw the character"
 * )
 *
 * @SWG\Property(
 *     type="string",
 *     format="date-time",
 *     property="logoff_time",
 *     description="The last time when the character signed out"
 * )
 *
 * @SWG\Property(
 *     type="integer",
 *     format="int64",
 *     property="location_id",
 *     description="The place where the character is"
 * )
 *
 * @SWG\Property(
 *     type="integer",
 *     property="ship_type_id",
 *     description="The typeID of the ship into which the character is in"
 * )
 *
 * @SWG\Property(
 *     type="object",
 *     property="ship_type",
 *     description="The ship information",
 *     @SWG\Property(
 *          type="integer",
 *          property="typeID",
 *          description="The type ID of the ship"
 *     ),
 *     @SWG\Property(
 *          type="integer",
 *          property="groupID",
 *          description="The group ID of the ship"
 *     ),
 *     @SWG\Property(
 *          type="string",
 *          property="typeName",
 *          description="The name of the ship (item)"
 *     ),
 *     @SWG\Property(
 *          type="string",
 *          property="description",
 *          description="The description of the ship"
 *     ),
 *     @SWG\Property(
 *          type="number",
 *          format="double",
 *          property="mass",
 *          description="The mass of the ship"
 *     ),
 *     @SWG\Property(
 *          type="number",
 *          format="double",
 *          property="volume",
 *          description="The volume of the ship"
 *     ),
 *     @SWG\Property(
 *          type="number",
 *          format="double",
 *          property="capacity",
 *          description="The cargo capacity of the ship"
 *     ),
 *     @SWG\Property(
 *          type="integer",
 *          property="portionSize",
 *          description="The portion size of the ship"
 *     ),
 *     @SWG\Property(
 *          type="integer",
 *          property="raceID",
 *          description="The ID of the race to which the asset is related"
 *     ),
 *     @SWG\Property(
 *          type="number",
 *          format="float",
 *          property="basePrice",
 *          description="The initial price as state by CCP"
 *     ),
 *     @SWG\Property(
 *          type="boolean",
 *          property="published",
 *          description="Determine if the item is available in-game"
 *     ),
 *     @SWG\Property(
 *          type="integer",
 *          property="marketGroupID",
 *          description="The ID of the group on the market"
 *     ),
 *     @SWG\Property(
 *          type="number",
 *          format="double",
 *          property="iconID",
 *          description="The asset icon ID"
 *     ),
 *     @SWG\Property(
 *          type="number",
 *          format="double",
 *          property="soundID",
 *          description="The asset sound ID"
 *     ),
 *     @SWG\Property(
 *          type="number",
 *          format="double",
 *          property="graphicID",
 *          description="The asset graphic ID"
 *     )
 * )
 */
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

        return $this->belongsTo(InvType::class, 'ship_type_id', 'typeID');
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     * @deprecated
     */
    public function user()
    {

        return $this->belongsTo(User::class, 'character_id', 'id');
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function location()
    {
        // System range
        if ($this->location_id >= 30000001 && $this->location_id <= 31002604)
            return $this->belongsTo(MapDenormalize::class, 'location_id', 'itemID');

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
