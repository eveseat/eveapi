<?php

/*
 * This file is part of SeAT
 *
 * Copyright (C) 2015, 2016, 2017, 2018, 2019  Leon Jacobs
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
use Seat\Eveapi\Models\Sde\InvType;
use Seat\Eveapi\Models\Sde\MapDenormalize;
use Seat\Eveapi\Models\Universe\UniverseStructure;
use Seat\Eveapi\Traits\HasCompositePrimaryKey;

/**
 * Class CorporationStructure.
 * @package Seat\Eveapi\Models\Corporation
 */
class CorporationStructure extends Model
{
    use HasCompositePrimaryKey;

    const DGM_SERVICE_MODULE_CYCLE_FUEL_NEED = 2109;

    const DGM_SERVICE_MODULE_ACTIVATION_FUEL_NEED = 2110;

    /**
     * @var bool
     */
    protected static $unguarded = true;

    /**
     * @var array
     */
    protected $primaryKey = ['corporation_id', 'structure_id'];

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasOne
     */
    public function info()
    {

        return $this->hasOne(UniverseStructure::class, 'structure_id', 'structure_id');
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function services()
    {

        return $this->hasMany(CorporationStructureService::class, 'structure_id', 'structure_id');
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

        return $this->hasOne(MapDenormalize::class, 'itemID', 'system_id');
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function items()
    {

        return $this->hasMany(CorporationAsset::class,
            'location_id', 'structure_id');
    }

    /**
     * @return \Illuminate\Support\Collection
     */
    public function getHighSlotsAttribute()
    {
        return $this->items->filter(function ($value) {
            return strpos($value->location_flag, 'HiSlot') !== false;
        });
    }

    /**
     * @return \Illuminate\Support\Collection
     */
    public function getMediumSlotsAttribute()
    {
        return $this->items->filter(function ($value) {
            return strpos($value->location_flag, 'MedSlot') !== false;
        });
    }

    /**
     * @return \Illuminate\Support\Collection
     */
    public function getLowSlotsAttribute()
    {
        return $this->items->filter(function ($value) {
            return strpos($value->location_flag, 'LoSlot') !== false;
        });
    }

    /**
     * @return \Illuminate\Support\Collection
     */
    public function getRigSlotsAttribute()
    {
        return $this->items->filter(function ($value) {
            return strpos($value->location_flag, 'RigSlot') !== false;
        });
    }

    /**
     * @return mixed
     */
    public function getServicesSlotsAttribute()
    {
        return $this->items->filter(function ($value) {
            return strpos($value->location_flag, 'ServiceSlot') !== false;
        });
    }

    /**
     * @return \Illuminate\Support\Collection
     */
    public function getFightersBayAttribute()
    {
        return $this->items->filter(function ($value) {
            return strpos($value->location_flag, 'Fighter') !== false;
        });
    }

    /**
     * @return \Illuminate\Support\Collection
     */
    public function getAmmoHoldAttribute()
    {
        return $this->items->where('location_flag', 'Cargo');
    }

    /**
     * @return \Illuminate\Support\Collection
     */
    public function getFuelBayAttribute()
    {
        return $this->items->where('location_flag', 'StructureFuel');
    }

    /**
     * @return float
     */
    public function getFuelAttribute()
    {
        return $this->items->where('location_flag', 'StructureFuel')->sum('quantity');
    }

    /**
     * @return float
     */
    public function getFuelConsumptionAttribute()
    {
        return $this->items->sum(function ($item) {
            return $item->type->dogma_attributes->where('attributeID', self::DGM_SERVICE_MODULE_CYCLE_FUEL_NEED)
                ->sum('valueFloat');
        });
    }

    /**
     * @return float
     */
    public function getActivationFuelConsumptionAttribute()
    {
        return $this->items->sum(function ($item) {
            return $item->type->dogma_attributes->where('attributeID', self::DGM_SERVICE_MODULE_ACTIVATION_FUEL_NEED)
                ->sum('valueFloat');
        });
    }

    /**
     * @return float
     */
    public function getEstimatedPriceAttribute()
    {
        return $this->type->price->average_price + $this->items->sum(function ($item) {
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
        return sprintf('[%s, %s]', $this->type->typeName, $this->info->name) . PHP_EOL .

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

            $this->services_slots->map(function ($slot) {
                return sprintf('%s x%d', $slot->type->typeName, $slot->quantity);
            })->implode(PHP_EOL) .

            PHP_EOL . PHP_EOL .

            $this->rig_slots->map(function ($slot) {
                return sprintf('%s x%d', $slot->type->typeName, $slot->quantity);
            })->implode(PHP_EOL) .

            PHP_EOL . PHP_EOL .

            $this->ammo_hold->map(function ($slot) {
                return sprintf('%s x%d', $slot->type->typeName, $slot->quantity);
            })->implode(PHP_EOL) .

            PHP_EOL . PHP_EOL .

            $this->fighters_bay->map(function ($slot) {
                return sprintf('%s x%d', $slot->type->typeName, $slot->quantity);
            })->implode(PHP_EOL) .

            PHP_EOL . PHP_EOL;
    }
}
