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
 * Class KillmailAttacker.
 *
 * @package Seat\Eveapi\Models\Killmails
 *
 * @OA\Property(
 *     property="attacker_hash",
 *     type="string",
 *     description="A hash composite of character_id, corporation_id, alliance_id and faction_id fields"
 * )
 *
 * @OA\Property(
 *     type="integer",
 *     format="int64",
 *     property="character_id",
 *     description="The character identifier"
 * )
 *
 * @OA\Property(
 *     type="integer",
 *     format="int64",
 *     property="corporation_id",
 *     description="The corporation identifier to which the attacker depends"
 * )
 *
 * @OA\Property(
 *     type="integer",
 *     format="int64",
 *     property="alliance_id",
 *     description="The alliance identifier to which the attacker depends"
 * )
 *
 * @OA\Property(
 *     type="integer",
 *     property="faction_id",
 *     description="The faction identifier to which the attacker depends (if factional warfare)"
 * )
 *
 * @OA\Property(
 *     type="number",
 *     format="float",
 *     property="security_status",
 *     description="The attacker security status"
 * )
 *
 * @OA\Property(
 *     type="boolean",
 *     property="final_blow",
 *     description="True if the attacker did the final blow"
 * )
 *
 * @OA\Property(
 *     type="integer",
 *     property="damage_done",
 *     description="The amount of damage the attacker applied"
 * )
 *
 * @OA\Property(
 *     type="integer",
 *     property="ship_type_id",
 *     description="The ship inventory type identifier into which attacker was"
 * )
 *
 * @OA\Property(
 *     type="integer",
 *     property="weapon_type_id",
 *     description="The weapon inventory type identifier used by the attacker"
 * )
 */
#[OA\Schema(
    title: 'KillmailAttacker',
    description: 'Killmail Attacker',
    type: 'object'
)]
class KillmailAttacker extends Model
{
    protected $casts = [
        'final_blow' => 'boolean',
    ];

    /**
     * @var array
     */
    protected $hidden = ['id', 'killmail_id', 'created_at', 'updated_at'];

    /**
     * @var bool
     */
    protected static $unguarded = true;

    protected static function boot()
    {
        parent::boot();

        // generate unique hash for the model based on attacker meta-data
        self::creating(function ($model) {
            $model->attacker_hash = md5(serialize([
                $model->character_id,
                $model->corporation_id,
                $model->alliance_id,
                $model->faction_id,
            ]));
        });
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
     * @return \Illuminate\Database\Eloquent\Relations\HasOne
     */
    public function ship()
    {
        return $this->hasOne(InvType::class, 'typeID', 'ship_type_id')
            ->withDefault();
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasOne|null
     */
    public function weapon()
    {
        return $this->hasOne(InvType::class, 'typeID', 'weapon_type_id');
    }
}
