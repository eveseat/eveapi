<?php

/*
 * This file is part of SeAT
 *
 * Copyright (C) 2015 to 2022 Leon Jacobs
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
use Seat\Eveapi\Models\Assets\CorporationAsset;
use Seat\Eveapi\Models\Sde\InvType;
use Seat\Eveapi\Models\Sde\SolarSystem;
use Seat\Eveapi\Models\Universe\UniverseStructure;

#[OA\Schema(
    title: 'CorporationStructure',
    description: 'Corporation Structure',
    properties: [
        new OA\Property(property: 'structure_id', description: 'Structure unique identifier', type: 'integer', format: 'int64'),
        new OA\Property(property: 'profile_id', description: 'Security profile unique identifier applied to the structure', type: 'integer'),
        new OA\Property(property: 'fuel_expires', description: 'Date/time when the structure will reach out of fuel', type: 'string', format: 'date-time'),
        new OA\Property(property: 'state_timer_start', description: 'Date/time when the current structure state has started', type: 'string', format: 'date-time'),
        new OA\Property(property: 'state_timer_end', description: 'Date/Time when the current structure state will be over', type: 'string', format: 'date-time'),
        new OA\Property(property: 'unanchors_at', description: 'Date/Time when the structure will be unanchored - if applicable', type: 'string', format: 'date-time'),
        new OA\Property(property: 'state', description: 'Current structure state', type: 'string', enum: ['anchor_vulnerable', 'anchoring', 'armor_reinforce', 'armor_vulnerable', 'fitting_invulnerable', 'hull_reinforce', 'hull_vulnerable', 'online_deprecated', 'onlining_vulnerable', 'shield_vulnerable', 'unanchored', 'unknown']),
        new OA\Property(property: 'reinforce_weekday', description: 'Current structure reinforce weekday', type: 'integer'),
        new OA\Property(property: 'reinforce_hour', description: 'Current structure reinforce hour', type: 'integer'),
        new OA\Property(property: 'next_reinforce_weekday', description: 'Next weekday when the structure will be reinforceable', type: 'integer'),
        new OA\Property(property: 'next_reinforce_hour', description: 'Next hour when the structure will be reinforceable', type: 'integer'),
        new OA\Property(property: 'next_reinforce_apply', description: 'Date/Time when the structure will be reinforceable', type: 'string', format: 'date-time'),
        new OA\Property(property: 'info', ref: '#/components/schemas/UniverseStructure'),
        new OA\Property(property: 'type', ref: '#/components/schemas/InvType'),
        new OA\Property(property: 'services', type: 'array', items: new OA\Items(ref: '#/components/schemas/CorporationStructureService')),
        new OA\Property(property: 'solar_system', ref: '#/components/schemas/SolarSystem')
    ],
    type: 'object'
)]
class CorporationStructure extends Model
{
    const DGM_SERVICE_MODULE_CYCLE_FUEL_NEED = 2109;

    const DGM_SERVICE_MODULE_ACTIVATION_FUEL_NEED = 2110;

    const DGM_STRUCTURE_SERVICE_ROLE_BONUS = 2339;

    /**
     * @var array
     */
    protected $hidden = ['corporation_id', 'type_id', 'system_id', 'created_at', 'updated_at'];

    /**
     * @var bool
     */
    protected static $unguarded = true;

    /**
     * @var array
     */
    protected $primaryKey = 'structure_id';

    /**
     * @var bool
     */
    public $incrementing = false;

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
        $use = $this->items->sum(function ($item) {
            return $item->type->dogma_attributes->where('attributeID', self::DGM_SERVICE_MODULE_CYCLE_FUEL_NEED)
                ->sum('valueFloat');
        });

        $reduction = $this->type->dogma_attributes->where('attributeID', self::DGM_STRUCTURE_SERVICE_ROLE_BONUS)->first();
        if ($reduction) {
            $use = $use * ((100 + $reduction->valueFloat) / 100);
        }

        return $use;
    }

    /**
     * @return float
     */
    public function getActivationFuelConsumptionAttribute()
    {
        $use = $this->items->sum(function ($item) {
            return $item->type->dogma_attributes->where('attributeID', self::DGM_SERVICE_MODULE_ACTIVATION_FUEL_NEED)
                ->sum('valueFloat');
        });

        $reduction = $this->type->dogma_attributes->where('attributeID', self::DGM_STRUCTURE_SERVICE_ROLE_BONUS)->first();
        if ($reduction) {
            $use = $use * ((100 + $reduction->valueFloat) / 100);
        }

        return $use;
    }

    /**
     * @return float
     */
    public function getEstimatedPriceAttribute()
    {
        return $this->type->price->average + $this->items->sum(function ($item) {
                return $item->type->price->average * $item->quantity;
            });
    }

    /**
     * @return float
     */
    public function getFittingEstimatedPriceAttribute()
    {
        return $this->items->sum(function ($item) {
            return $item->type->price->average * $item->quantity;
        });
    }

    /**
     * @param $value
     */
    public function setFuelExpiresAttribute($value)
    {
        $this->attributes['fuel_expires'] = is_null($value) ? null : carbon($value);
    }

    /**
     * @param $value
     */
    public function setStateTimerStartAttribute($value)
    {
        $this->attributes['state_timer_start'] = is_null($value) ? null : carbon($value);
    }

    /**
     * @param $value
     */
    public function setStateTimerEndAttribute($value)
    {
        $this->attributes['state_timer_end'] = is_null($value) ? null : carbon($value);
    }

    /**
     * @param $value
     */
    public function setUnanchorsAtAttribute($value)
    {
        $this->attributes['unanchors_at'] = is_null($value) ? null : carbon($value);
    }

    /**
     * @param $value
     */
    public function setNextReinforceApplyAttribute($value)
    {
        $this->attributes['next_reinforce_apply'] = is_null($value) ? null : carbon($value);
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasOne
     */
    public function info()
    {

        return $this->hasOne(UniverseStructure::class, 'structure_id', 'structure_id')
            ->withDefault([
                'name' => trans('web::seat.unknown'),
            ]);
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
    public function solar_system()
    {
        return $this->hasOne(SolarSystem::class, 'system_id', 'system_id');
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
