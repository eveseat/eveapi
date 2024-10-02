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

namespace Seat\Eveapi\Models\Location;

use Seat\Eveapi\Models\Assets\CharacterAsset;
use Seat\Eveapi\Models\Sde\InvGroup;
use Seat\Eveapi\Models\Sde\InvType;
use Seat\Services\Contracts\HasTypeID;
use Seat\Services\Models\ExtensibleModel;

/**
 * Class CharacterShip.
 *
 * @package Seat\Eveapi\Models\Location
 */
class CharacterShip extends ExtensibleModel implements HasTypeID
{
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
    protected $primaryKey = 'character_id';

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function content()
    {
        return $this->hasMany(CharacterAsset::class, 'location_id', 'ship_item_id');
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasOne
     */
    public function type()
    {
        return $this->hasOne(InvType::class, 'typeID', 'ship_type_id')
            ->withDefault(function ($type) {
                $group = new InvGroup();
                $group->groupName = 'Unknown';

                $type->typeName = trans('web::seat.unknown');
                $type->group = $group;
            });
    }

    /**
     * @return int The eve type id of this object
     */
    public function getTypeID(): int
    {
        return $this->ship_type_id;
    }
}
