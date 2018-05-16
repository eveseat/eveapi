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

namespace Seat\Eveapi\Models\Character;

use Illuminate\Database\Eloquent\Model;
use Seat\Eveapi\Models\Location\CharacterShip;
use Seat\Eveapi\Models\Wallet\CharacterWalletBalance;
use Seat\Services\Traits\NotableTrait;

/**
 * Class CharacterInfo.
 * @package Seat\Eveapi\Models\Character
 */
class CharacterInfo extends Model
{
    use NotableTrait;

    /**
     * @var bool
     */
    protected static $unguarded = true;

    /**
     * @var string
     */
    protected $primaryKey = 'character_id';

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function balance()
    {

        return $this->belongsTo(CharacterWalletBalance::class,
            'character_id', 'character_id');
    }

    /**
     * @return mixed
     */
    public function corporation()
    {

        return CharacterCorporationHistory::where('character_id', $this->character_id)
            ->orderBy('record_id', 'desc')
            ->first();
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function corporation_roles()
    {

        return $this->hasMany(CharacterRole::class, 'character_id', 'character_id');
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function corporation_history()
    {

        return $this->hasMany(CharacterCorporationHistory::class,
            'character_id', 'character_id');
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function skills()
    {

        return $this->hasMany(CharacterSkill::class,
            'character_id', 'character_id');
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasOne
     */
    public function ship()
    {

        return $this->hasOne(CharacterShip::class, 'character_id', 'character_id');
    }
}
