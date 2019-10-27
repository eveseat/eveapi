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

namespace Seat\Eveapi\Models\Fittings;

use Illuminate\Database\Eloquent\Model;
use Seat\Eveapi\Models\Sde\InvType;
use Seat\Eveapi\Traits\HasCompositePrimaryKey;

/**
 * Class CharacterFitting.
 * @package Seat\Eveapi\Models\Fittings
 */
class CharacterFitting extends Model
{
    use HasCompositePrimaryKey;

    /**
     * @var bool
     */
    protected static $unguarded = true;

    /**
     * @var array
     */
    protected $primaryKey = ['character_id', 'fitting_id'];

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function items()
    {

        return $this->hasMany(CharacterFittingItem::class,
            'fitting_id', 'fitting_id');
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasOne
     */
    public function ship()
    {

        return $this->hasOne(InvType::class, 'typeID', 'ship_type_id');
    }

    /**
     * @return \Illuminate\Support\Collection
     */
    public function getHighSlotsAttribute()
    {
        return $this->items->filter(function ($value) {
            return strpos($value->flag, 'HiSlot') !== false;
        });
    }

    /**
     * @return \Illuminate\Support\Collection
     */
    public function getMediumSlotsAttribute()
    {
        return $this->items->filter(function ($value) {
            return strpos($value->flag, 'MedSlot') !== false;
        });
    }

    /**
     * @return \Illuminate\Support\Collection
     */
    public function getLowSlotsAttribute()
    {
        return $this->items->filter(function ($value) {
            return strpos($value->flag, 'LoSlot') !== false;
        });
    }

    /**
     * @return \Illuminate\Support\Collection
     */
    public function getRigSlotsAttribute()
    {
        return $this->items->filter(function ($value) {
            return strpos($value->flag, 'RigSlot') !== false;
        });
    }

    /**
     * @return \Illuminate\Support\Collection
     */
    public function getSubSystemsAttribute()
    {
        return $this->items->filter(function ($value) {
            return strpos($value->flag, 'SubSystemSlot') !== false;
        });
    }

    /**
     * @return \Illuminate\Support\Collection
     */
    public function getDronesBayAttribute()
    {
        return $this->items->where('flag', 'DroneBay');
    }

    /**
     * @return \Illuminate\Support\Collection
     */
    public function getFightersBayAttribute()
    {
        return $this->items->where('flag', 'FighterBay');
    }

    /**
     * @return \Illuminate\Support\Collection
     */
    public function getCargoAttribute()
    {
        return $this->items->where('flag', 'Cargo');
    }

    /**
     * @return float
     */
    public function getEstimatedPriceAttribute()
    {
        return $this->ship->price->average_price + $this->items->sum(function ($item) {
            return $item->type->price->average_price * $item->quantity;
        });
    }

    /**
     * @return float
     */
    public function getFittingEstimatedPriceAttribute()
    {
        return $this->items->sum(function ($item) {
            return $item->type->price->average_price * $item->quantity;
        });
    }

    /**
     * @return string
     */
    public function toEve()
    {
        return sprintf('[%s, %s]', $this->ship->typeName, $this->name) . PHP_EOL .

        $this->low_slots->map(function ($slot) {
            return sprintf('%s x%d', $slot->type->typeName, $slot->quantity);
        })->implode(PHP_EOL) .

        PHP_EOL . PHP_EOL .

        $this->medium_slots->map(function ($slot) {
            return sprintf('%s x%d', $slot->type->typeName, $slot->quantity);
        })->implode(PHP_EOL) .

        PHP_EOL . PHP_EOL .

        $this->high_slots->map(function ($slot) {
            return sprintf('%s x%d', $slot->type->typeName, $slot->quantity);
        })->implode(PHP_EOL) .

        PHP_EOL . PHP_EOL .

        $this->sub_systems->map(function ($slot) {
            return sprintf('%s x%d', $slot->type->typeName, $slot->quantity);
        })->implode(PHP_EOL) .

        PHP_EOL . PHP_EOL .

        $this->rig_slots->map(function ($slot) {
            return sprintf('%s x%d', $slot->type->typeName, $slot->quantity);
        })->implode(PHP_EOL) .

        PHP_EOL . PHP_EOL .

        $this->drones_bay->map(function ($slot) {
            return sprintf('%s x%d', $slot->type->typeName, $slot->quantity);
        })->implode(PHP_EOL) .

        PHP_EOL . PHP_EOL .

        $this->cargo->map(function ($slot) {
            return sprintf('%s x%d', $slot->type->typeName, $slot->quantity);
        })->implode(PHP_EOL) .

        PHP_EOL . PHP_EOL .

        $this->fighters_bay->map(function ($slot) {
            return sprintf('%s x%d', $slot->type->typeName, $slot->quantity);
        })->implode(PHP_EOL) .

        PHP_EOL . PHP_EOL;
    }
}
