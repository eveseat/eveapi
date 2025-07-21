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

namespace Seat\Eveapi\Models\Killmails;

use OpenApi\Attributes as OA;
use Seat\Eveapi\Models\Sde\InvType;
use Seat\Eveapi\Models\Universe\UniverseName;
use Seat\Services\Models\ExtensibleModel;

#[OA\Schema(
    title: 'KillmailVictim',
    description: 'Killmail Victim',
    properties: [
        new OA\Property(property: 'character_id', description: 'The killed character identifier', type: 'integer', format: 'int64'),
        new OA\Property(property: 'corporation_id', description: 'The killed character corporation identifier', type: 'integer', format: 'int64'),
        new OA\Property(property: 'alliance_id', description: 'The killed character alliance identifier', type: 'integer', format: 'int64'),
        new OA\Property(property: 'faction_id', description: 'The killed character faction identifier', type: 'integer', format: 'int64'),
        new OA\Property(property: 'damage_taken', description: 'The damage amount the killed character get', type: 'integer'),
        new OA\Property(property: 'ship_type_id', description: 'The destroyed ship inventory type identifier', type: 'integer'),
        new OA\Property(property: 'x', description: 'The x coordinate where the kill occurs', type: 'number', format: 'double'),
        new OA\Property(property: 'y', description: 'The y coordinate where the kill occurs', type: 'number', format: 'double'),
        new OA\Property(property: 'z', description: 'The z coordinate where the kill occurs', type: 'number', format: 'double'),
    ],
    type: 'object'
)]
class KillmailVictim extends ExtensibleModel
{
    /**
     * @var array
     */
    protected $hidden = ['killmail_id', 'created_at', 'updated_at'];

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
    protected $primaryKey = 'killmail_id';

    /**
     * @return float
     */
    public function getFittedEstimateAttribute(): float
    {
        return $this->items->sum(function ($item) {
            return ($item->pivot->quantity_dropped + $item->pivot->quantity_destroyed) * $item->price->average_price;
        });
    }

    /**
     * @return float
     */
    public function getDroppedEstimateAttribute(): float
    {
        return $this->items->sum(function ($item) {
            return $item->pivot->quantity_dropped * $item->price->average_price;
        });
    }

    /**
     * @return float
     */
    public function getDestroyedEstimateAttribute(): float
    {
        return $this->items->sum(function ($item) {
            return $item->pivot->quantity_destroyed * $item->price->average_price;
        }) + $this->ship->price->average_price;
    }

    /**
     * @return float
     */
    public function getTotalEstimateAttribute(): float
    {
        return $this->dropped_estimate + $this->destroyed_estimate;
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasOne
     */
    public function ship()
    {

        return $this->hasOne(InvType::class, 'typeID', 'ship_type_id')
            ->withDefault([
                'typeName' => trans('web::seat.unknown'),
            ]);
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasOne
     */
    public function character()
    {

        return $this->hasOne(UniverseName::class, 'entity_id', 'character_id')
            ->withDefault([
                'name' => trans('web::seat.unknown'),
                'category' => 'character',
            ]);
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasOne
     */
    public function corporation()
    {

        return $this->hasOne(UniverseName::class, 'entity_id', 'corporation_id')
            ->withDefault([
                'name' => trans('web::seat.unknown'),
                'category' => 'corporation',
            ]);
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasOne
     */
    public function alliance()
    {

        return $this->hasOne(UniverseName::class, 'entity_id', 'alliance_id')
            ->withDefault([
                'name' => trans('web::seat.unknown'),
                'category' => 'alliance',
            ]);
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasOne
     */
    public function faction()
    {
        return $this->hasOne(UniverseName::class, 'entity_id', 'faction_id')
            ->withDefault([
                'name' => trans('web::seat.unknown'),
                'category' => 'faction',
            ]);
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
     */
    public function items()
    {
        return $this->belongsToMany(InvType::class, 'killmail_victim_items', 'killmail_id', 'item_type_id', 'killmail_id', 'typeID')
            ->using(KillmailVictimItem::class)
            ->withPivot(['quantity_destroyed', 'quantity_dropped', 'singleton', 'flag']);
    }
}
