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

namespace Seat\Eveapi\Models\Killmails;

use Illuminate\Database\Eloquent\Model;
use Seat\Eveapi\Models\Sde\MapDenormalize;

/**
 * Class KillmailDetail.
 * @package Seat\Eveapi\Models\Killmails
 *
 * @SWG\Definition(
 *     description="Killmail Detail",
 *     title="KillmailDetail",
 *     type="object"
 * )
 *
 * @SWG\Property(
 *     type="string",
 *     format="date-time",
 *     property="killmail_time",
 *     description="The date-time when kill append"
 * )
 *
 * @SWG\Property(
 *     type="integer",
 *     property="solar_system_id",
 *     description="The solar system identifier in which the kill occurs"
 * )
 *
 * @SWG\Property(
 *     type="integer",
 *     property="moon_id",
 *     description="The moon identifier near to which the kill occurs"
 * )
 *
 * @SWG\Property(
 *     type="integer",
 *     format="int64",
 *     property="war_id",
 *     description="The war identifier in which the kill involves"
 * )
 *
 * @SWG\Property(
 *     type="array",
 *     property="attackers",
 *     description="A list of attackers",
 *     @SWG\Items(ref="#/definitions/KillmailAttacker")
 * )
 *
 * @SWG\Property(
 *     property="victims",
 *     description="The victim",
 *     ref="#/definitions/KillmailVictim"
 * )
 */
class KillmailDetail extends Model
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
    public function victims()
    {

        return $this->hasOne(KillmailVictim::class, 'killmail_id', 'killmail_id');
    }

    public function solar_system()
    {

        return $this->hasOne(MapDenormalize::class, 'itemID', 'solar_system_id');
    }
}
