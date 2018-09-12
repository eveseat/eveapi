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

namespace Seat\Eveapi\Models\Industry;

use Illuminate\Database\Eloquent\Model;
use Seat\Eveapi\Models\Sde\InvType;
use Seat\Eveapi\Models\Sde\MapDenormalize;
use Seat\Eveapi\Traits\HasCompositePrimaryKey;

/**
 * Class CharacterMining.
 * @package Seat\Eveapi\Models\Industry
 */
class CharacterMining extends Model
{
    use HasCompositePrimaryKey;

    /**
     * @var bool
     */
    protected static $unguarded = true;

    /**
     * @var array
     */
    protected $primaryKey = ['character_id', 'date', 'time', 'solar_system_id', 'type_id'];

    /**
     * @var array
     */
    protected $appends = [
        'amount', 'volumes',
    ];

    /**
     * @return float
     */
    public function getAmountAttribute()
    {

        if (is_null($this->type))
            return 0.0;

        if (is_null($this->type->prices))
            return 0.0;

        return $this->quantity * $this->type->prices->average_price;
    }

    /**
     * @return float
     */
    public function getVolumesAttribute()
    {

        if (is_null($this->type))
            return 0.0;

        return $this->quantity * $this->type->volume;
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasOne
     */
    public function type()
    {

        return $this->hasOne(InvType::class, 'typeID', 'type_id');
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasOne
     */
    public function system()
    {

        return $this->hasOne(MapDenormalize::class, 'itemID', 'solar_system_id');
    }

    /**
     * @param array $options
     *
     * @return bool
     */
    public function save(array $options = [])
    {

        if (is_null($this->getAttributeValue('date')))
            $this->setAttribute('date', carbon()->toDateString());

        $this->setAttribute('month', carbon($this->getAttributeValue('date'))->month);
        $this->setAttribute('year', carbon($this->getAttributeValue('date'))->year);

        return parent::save($options);
    }
}
