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

namespace Seat\Eveapi\Models\Killmails;

use Illuminate\Database\Eloquent\Model;
use OpenApi\Attributes as OA;

#[OA\Schema(
    title: 'Killmail',
    description: 'Killmail information',
    properties: [
        new OA\Property(property: 'killmail_id', description: 'The unique Killmail identifier', type: 'integer', format: 'int64'),
        new OA\Property(property: 'killmail_hash', description: 'The killmail hash', type: 'string'),
        new OA\Property(property: 'detail', ref: '#/components/schemas/KillmailDetail'),
        new OA\Property(property: 'victim', ref: '#/components/schemas/KillmailVictim'),
        new OA\Property(property: 'attackers', type: 'array', items: new OA\Items(ref: '#/components/schemas/KillmailAttacker')),
    ],
    type: 'object'
)]
class Killmail extends Model
{
    /**
     * @var bool
     */
    public $incrementing = false;

    /**
     * @var array
     */
    protected $hidden = ['created_at', 'updated_at'];

    /**
     * @var bool
     */
    protected static $unguarded = true;

    /**
     * @var string
     */
    protected $primaryKey = 'killmail_id';

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasOne
     */
    public function detail()
    {
        return $this->hasOne(KillmailDetail::class, 'killmail_id', 'killmail_id')->withDefault([
            'killmail_time'   => '1970-01-01 00:00:01',
            'solar_system_id' => 30000380,
        ]);
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasOne
     */
    public function victim()
    {
        return $this->hasOne(KillmailVictim::class, 'killmail_id', 'killmail_id')->withDefault([
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
