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
use Seat\Eveapi\Models\Sde\SolarSystem;

/**
 * Class KillmailDetail.
 *
 * @package Seat\Eveapi\Models\Killmails
 *
 * @OA\Schema(
 *     description="Killmail Detail",
 *     title="KillmailDetail",
 *     type="object"
 * )
 *
 * @OA\Property(
 *     property="killmail_time",
 *     type="string",
 *     format="date-time",
 *     description="The date-time when kill append"
 * )
 *
 * @OA\Property(
 *     property="solar_system_id",
 *     type="integer",
 *     description="The solar system identifier in which the kill occurs"
 * )
 *
 * @OA\Property(
 *     property="moon_id",
 *     type="integer",
 *     description="The moon identifier near to which the kill occurs"
 * )
 *
 * @OA\Property(
 *     property="war_id",
 *     type="integer",
 *     format="int64",
 *     description="The war identifier in which the kill involves"
 * )
 */
class KillmailDetail extends Model
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
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function attackers()
    {

        return $this->hasMany(KillmailAttacker::class, 'killmail_id', 'killmail_id');
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasOne
     */
    public function victim()
    {

        return $this->hasOne(KillmailVictim::class, 'killmail_id', 'killmail_id')
            ->withDefault([
                'character_id'   => 0,
                'corporation_id' => 0,
                'ship_type_id'   => 0,
            ]);
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasOne
     */
    public function solar_system()
    {
        return $this->hasOne(SolarSystem::class, 'system_id', 'solar_system_id');
    }
}
