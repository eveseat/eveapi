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
        return $this->ship->price->adjusted_price + $this->items->sum(function ($item) {
            return $item->type->price->adjusted_price * $item->quantity;
        });
    }

    /**
     * @return float
     */
    public function getFittingEstimatedPriceAttribute()
    {
        return $this->items->sum(function ($item) {
            return $item->type->price->adjusted_price * $item->quantity;
        });
    }

    /**
     * @return string
     */
    public function toEve()
    {
        $sheet = sprintf('[%s, %s]', $this->ship->typeName, $this->name);
        $low_slots = [];
        $med_slots = [];
        $high_slots = [];
        $sub_systems = [];
        $rigs_slots = [];
        $drones = [];
        $fighters = [];
        $cargo = [];

        foreach ($this->low_slots as $slot) {
            $low_slots[] = $slot->type->typeName;
        }

        foreach ($this->medium_slots as $slot) {
            $med_slots[] = $slot->type->typeName;
        }

        foreach ($this->high_slots as $slot) {
            $high_slots[] = $slot->type->typeName;
        }

        foreach ($this->sub_systems as $slot) {
            $sub_systems[] = $slot->type->typeName;
        }

        foreach ($this->rig_slots as $slot) {
            $rigs_slots[] = $slot->type->typeName;
        }

        foreach ($this->drones_bay as $slot) {
            $drones[] = sprintf('%s x%d', $slot->type->typeName, $slot->quantity);
        }

        foreach ($this->cargo as $slot) {
            $cargo[] = sprintf('%s x%d', $slot->type->typeName, $slot->quantity);
        }

        foreach ($this->fighters_bay as $slot) {
            $fighters[] = sprintf('%s x%d', $slot->type->typeName, $slot->quantity);
        }

        return $sheet . PHP_EOL .
            implode(PHP_EOL, $low_slots) . PHP_EOL . PHP_EOL .
            implode(PHP_EOL, $med_slots) . PHP_EOL . PHP_EOL .
            implode(PHP_EOL, $high_slots) . PHP_EOL . PHP_EOL .
            implode(PHP_EOL, $rigs_slots) . PHP_EOL . PHP_EOL .
            implode(PHP_EOL, $sub_systems) . PHP_EOL . PHP_EOL .
            implode(PHP_EOL, $drones) . PHP_EOL . PHP_EOL .
            implode(PHP_EOL, $cargo) . PHP_EOL . PHP_EOL .
            implode(PHP_EOL, $fighters) . PHP_EOL . PHP_EOL;
    }
}
