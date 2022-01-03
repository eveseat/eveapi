<?php

/*
 * This file is part of SeAT
 *
 * Copyright (C) 2015 to 2021 Leon Jacobs
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

use Illuminate\Database\Eloquent\Model;
use OpenApi\Annotations as OA;
use Seat\Eveapi\Models\Sde\InvType;
use Seat\Eveapi\Models\Universe\UniverseName;

/**
 * Class KillmailVictim.
 *
 * @package Seat\Eveapi\Models\Killmails
 *
 * @OA\Schema(
 *     description="Killmail Victim",
 *     title="KillmailVictim",
 *     type="object"
 * )
 *
 * @OA\Property(
 *     type="integer",
 *     format="int64",
 *     property="character_id",
 *     description="The killed character identified"
 * )
 *
 * @OA\Property(
 *     type="integer",
 *     format="int64",
 *     property="corporation_id",
 *     description="The killed character corporation identifier"
 * )
 *
 * @OA\Property(
 *     type="integer",
 *     format="int64",
 *     property="alliance_id",
 *     description="The killed character alliance identifier (if any)"
 * )
 *
 * @OA\Property(
 *     type="integer",
 *     property="faction_id",
 *     description="The killed character faction identifier (if factional warfare)"
 * )
 *
 * @OA\Property(
 *     type="integer",
 *     property="damage_taken",
 *     description="The damage amount the killed character get"
 * )
 *
 * @OA\Property(
 *     type="integer",
 *     property="ship_type_id",
 *     description="The destroyed ship inventory type identifier"
 * )
 *
 * @OA\Property(
 *     type="number",
 *     format="double",
 *     property="x",
 *     description="The x coordinate where the kill occurs"
 * )
 *
 * @OA\Property(
 *     type="number",
 *     format="double",
 *     property="y",
 *     description="The y coordinate where the kill occurs"
 * )
 *
 * @OA\Property(
 *     type="number",
 *     format="double",
 *     property="z",
 *     description="The z coordinate where the kill occurs"
 * )
 */
class KillmailVictim extends Model
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
                'name'      => trans('web::seat.unknown'),
                'category'  => 'character',
            ]);
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasOne
     */
    public function corporation()
    {

        return $this->hasOne(UniverseName::class, 'entity_id', 'corporation_id')
            ->withDefault([
                'name'      => trans('web::seat.unknown'),
                'category'  => 'corporation',
            ]);
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasOne
     */
    public function alliance()
    {

        return $this->hasOne(UniverseName::class, 'entity_id', 'alliance_id')
            ->withDefault([
                'name'      => trans('web::seat.unknown'),
                'category'  => 'alliance',
            ]);
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasOne
     */
    public function faction()
    {
        return $this->hasOne(UniverseName::class, 'entity_id', 'faction_id')
            ->withDefault([
                'name'     => trans('web::seat.unknown'),
                'category' => 'faction',
            ]);
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
     */
    public function items()
    {
        return $this->belongsToMany(InvType::class, 'killmail_victim_items', 'killmail_id', 'item_type_id', 'killmail_id', 'typeID')
            ->withPivot(['quantity_destroyed', 'quantity_dropped', 'singleton', 'flag']);
    }
}
