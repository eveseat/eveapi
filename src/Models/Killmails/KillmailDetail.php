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
use Seat\Eveapi\Models\Sde\SolarSystem;
use Seat\Services\Models\ExtensibleModel;

#[OA\Schema(
    title: 'KillmailDetail',
    description: 'Killmail Detail',
    properties: [
        new OA\Property(property: 'killmail_time', description: 'The date-time when kill append', type: 'string', format: 'date-time'),
        new OA\Property(property: 'solar_system_id', description: 'The Solar System Identifier in which the kill occurs', type: 'integer'),
        new OA\Property(property: 'moon_id', description: 'The moon identifier near to which the kill occurs', type: 'integer'),
        new OA\Property(property: 'war_id', description: 'The war identifier in which the kill involves', type: 'integer', format: 'int64'),
    ],
    type: 'object'
)]
class KillmailDetail extends ExtensibleModel
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
                'character_id' => 0,
                'corporation_id' => 0,
                'ship_type_id' => 0,
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
