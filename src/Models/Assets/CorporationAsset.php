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

namespace Seat\Eveapi\Models\Assets;

use Illuminate\Database\Eloquent\Model;
use Seat\Eveapi\Models\Sde\InvType;
use Seat\Eveapi\Traits\CanUpsertIgnoreReplace;

/**
 * Class CorporationAsset.
 * @package Seat\Eveapi\Models\Assets
 */
class CorporationAsset extends Model
{
    use CanUpsertIgnoreReplace;

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
    protected $primaryKey = 'item_id';

    /**
     * @param $value
     *
     * @return string
     */
    public function getNameAttribute($value)
    {

        if (is_null($value) || $value == '')
            return $this->type->typeName;

        return $value;
    }

    /**
     * Provide a rate of the used space based on item capacity and stored item volume.
     *
     * @return float
     */
    public function getUsedVolumeRateAttribute()
    {

        if ($this->type->capacity == 0)
            return 0.0;

        return $this->getUsedVolumeAttribute() / $this->type->capacity * 100;
    }

    /**
     * Provide the used space based on stored item volume.
     *
     * @return float
     */
    public function getUsedVolumeAttribute()
    {

        $content = $this->content;

        if (! is_null($content))
            return $content->sum(function ($item) {

                return $item->type->volume;
            });

        return 0.0;
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasOne
     */
    public function type()
    {

        return $this->hasOne(InvType::class, 'typeID', 'type_id');
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function container()
    {

        return $this->belongsTo(CorporationAsset::class, 'item_id', 'location_id');
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function content()
    {

        return $this->hasMany(CorporationAsset::class, 'location_id', 'item_id');
    }
}
