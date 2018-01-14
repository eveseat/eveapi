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

namespace Seat\Eveapi\Models\PlanetaryInteraction;

use Illuminate\Database\Eloquent\Model;
use Seat\Eveapi\Traits\HasCompositePrimaryKey;

class CharacterPlanetLink extends Model
{

    use HasCompositePrimaryKey;

    /**
     * @var bool
     */
    protected static $unguarded = true;

    /**
     * @var bool
     */
    public $incrementing = false;

    /**
     * @var array
     */
    protected $primaryKey = [
        'character_id', 'planet_id', 'source_pin_id', 'destination_pin_id'];

    /**
     * Return the planet installation to which the pin in attached
     *
     * @return \Seat\Eveapi\Traits\SurrogateBelongsTo
     * @throws \Seat\Eveapi\Exception\SurrogateKeyException
     */
    public function planet()
    {
        return $this->belongsTo(
            CharacterPlanet::class,
            ['character_id', 'planet_id'],
            ['character_id', 'planet_id']);
    }

    /**
     * Return the pin from which the link is starting
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasOne
     */
    public function source()
    {
        return $this->hasOne(
            CharacterPlanetPin::class,
            ['character_id', 'planet_id', 'pin_id'],
            ['character_id', 'planet_id', 'source_pin_id']);
    }

    /**
     * Return the pin to which the link is going
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasOne
     */
    public function destination()
    {
        return $this->hasOne(
            CharacterPlanetPin::class,
            ['character_id', 'planet_id', 'pin_id'],
            ['character_id', 'planet_id', 'destination_pin_id']);
    }
}
