<?php

/*
 * This file is part of SeAT
 *
 * Copyright (C) 2015, 2016, 2017, 2018  Leon Jacobs
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
use Seat\Eveapi\Models\Assets\CorporationAsset;
use Seat\Eveapi\Models\Sde\DgmTypeAttribute;
use Seat\Eveapi\Models\Sde\InvControlTowerResource;
use Seat\Eveapi\Models\Sde\InvType;
use Seat\Eveapi\Models\Sde\MapDenormalize;
use Seat\Eveapi\Traits\HasCompositePrimaryKey;

/**
 * Class CorporationStarbase.
 * @package Seat\Eveapi\Models\Corporation
 */
class CorporationStarbase extends Model
{
    use HasCompositePrimaryKey;

    /**
     * @var bool
     */
    protected static $unguarded = true;

    /**
     * @var array
     */
    protected $primaryKey = ['corporation_id', 'starbase_id'];

    /**
     * @return float
     */
    public function getBaseFuelUsageAttribute()
    {

        $resources = InvControlTowerResource::where('controlTowerTypeID', $this->type_id)
            ->whereBetween('resourceTypeID', [4000, 5000])// base fuel usage are between 4000 and 5000
            ->where('purpose', 1)
            ->first();

        if (! is_null($resources))
            return $resources->quantity;

        return 0.0;
    }

    /**
     * @return float
     */
    public function getBaseStrontiumUsageAttribute()
    {

        $resources = InvControlTowerResource::where('controlTowerTypeID', $this->type_id)
            ->where('resourceTypeID', 16275)// base strontium usage is 16275
            ->where('purpose', 4)
            ->first();

        if (! is_null($resources))
            return $resources->quantity;

        return 0.0;
    }

    /**
     * @return float
     */
    public function getStrontiumBaySizeAttribute()
    {

        $attributes = DgmTypeAttribute::where('typeID', $this->type_id)
            ->where('attributeID', 1233)// strontium bay attribute
            ->first();

        if (! is_null($attributes))
            return $attributes->valueFloat;

        return 0.0;
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasOne
     */
    public function detail()
    {

        return $this->hasOne(CorporationStarbaseDetail::class, 'starbase_id', 'starbase_id');
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function fuelBays()
    {

        return $this->hasMany(CorporationStarbaseFuel::class, 'starbase_id', 'starbase_id');
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function item()
    {

        return $this->belongsTo(CorporationAsset::class, 'starbase_id', 'item_id');
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function moon()
    {

        return $this->belongsTo(MapDenormalize::class, 'moon_id', 'itemID');
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function system()
    {

        return $this->belongsTo(MapDenormalize::class, 'system_id', 'itemID');
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function type()
    {

        return $this->belongsTo(InvType::class, 'type_id', 'typeID');
    }
}
