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

namespace Seat\Eveapi\Models\Killmails;

use Illuminate\Database\Eloquent\Model;
use Seat\Eveapi\Models\Universe\UniverseName;

/**
 * Class KillmailAttacker.
 * @package Seat\Eveapi\Models\Killmails
 *
 * @SWG\Definition(
 *     description="Killmail Attacker",
 *     title="KillmailAttacker",
 *     type="object"
 * )
 *
 * @SWG\Property(
 *     type="integer",
 *     format="int64",
 *     property="killmail_id",
 *     description="The killmail identifier to which the attacker is attached"
 * )
 *
 * @SWG\Property(
 *     type="integer",
 *     format="int64",
 *     property="character_id",
 *     description="The character identifier"
 * )
 *
 * @SWG\Property(
 *     type="integer",
 *     format="int64",
 *     property="corporation_id",
 *     description="The corporation identifier to which the attacker depends"
 * )
 *
 * @SWG\Property(
 *     type="integer",
 *     format="int64",
 *     property="alliance_id",
 *     description="The alliance identifier to which the attacker depends"
 * )
 *
 * @SWG\Property(
 *     type="integer",
 *     property="faction_id",
 *     description="The faction identifier to which the attacker depends (if factional warfare)"
 * )
 *
 * @SWG\Property(
 *     type="number",
 *     format="float",
 *     property="security_status",
 *     description="The attacker security status"
 * )
 *
 * @SWG\Property(
 *     type="boolean",
 *     property="final_blow",
 *     description="True if the attacker did the final blow"
 * )
 *
 * @SWG\Property(
 *     type="integer",
 *     property="damage_done",
 *     description="The amount of damage the attacker applied"
 * )
 *
 * @SWG\Property(
 *     type="integer",
 *     property="ship_type_id",
 *     description="The ship inventory type identifier into which attacker was"
 * )
 *
 * @SWG\Property(
 *     type="integer",
 *     property="weapon_type_id",
 *     description="The weapon inventory type identifier used by the attacker"
 * )
 *
 * @SWG\Property(
 *     type="string",
 *     format="date-time",
 *     property="created_at",
 *     description="The date-time when record has been created into SeAT"
 * )
 *
 * @SWG\Property(
 *     type="string",
 *     format="date-time",
 *     property="updated_at",
 *     description="The date-time when record has been updated into SeAT"
 * )
 */
class KillmailAttacker extends Model
{
    /**
     * @var bool
     */
    protected static $unguarded = true;

    /**
     * @var bool
     */
    public $incrementing = false;

    /**
     * @var null
     */
    protected $primaryKey = null;

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasOne
     */
    public function character()
    {

        return $this->hasOne(UniverseName::class, 'entity_id', 'character_id')
            ->withDefault([
                'entity_id' => 0,
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
                'entity_id' => 0,
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
                'entity_id' => 0,
                'name'      => trans('web::seat.unknown'),
                'category'  => 'alliance',
            ]);
    }
}
