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

namespace Seat\Eveapi\Models\Killmails;

use Illuminate\Database\Eloquent\Model;
use Seat\Eveapi\Traits\HasCompositePrimaryKey;

/**
 * Class CorporationKillmail.
 * @package Seat\Eveapi\Models\Killmails
 *
 * @SWG\Definition(
 *     description="Character Killmail",
 *     title="CharacterKillmail",
 *     type="object"
 * )
 *
 * @SWG\Property(
 *     type="integer",
 *     format="int64",
 *     property="killmail_id",
 *     description="The killmail identifier"
 * )
 *
 * @SWG\Property(
 *     type="string",
 *     property="killmail_hash",
 *     description="The killmail hash"
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
 *
 * @deprecated 4.0.0
 */
class CorporationKillmail extends Model
{
    use HasCompositePrimaryKey;

    /**
     * @var bool
     */
    protected static $unguarded = true;

    /**
     * @var array
     */
    protected $primaryKey = ['corporation_id', 'killmail_id'];

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasOne
     */
    public function detail()
    {

        return $this->hasOne(KillmailDetail::class, 'killmail_id', 'killmail_id')
            ->withDefault([
                'killmail_time'   => '1970-01-01 00:00:01',
                'solar_system_id' => 30000380,
            ]);
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
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function attackers()
    {

        return $this->hasMany(KillmailAttacker::class, 'killmail_id', 'killmail_id');
    }
}
