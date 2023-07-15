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

namespace Seat\Tests\Eveapi\Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Seat\Eveapi\Models\Character\CharacterAffiliation;

/**
 * Class CharacterAffiliationFactory.
 * @package Seat\Tests\Web\Database\Factories
 */
class CharacterAffiliationFactory extends Factory
{
    protected $model = CharacterAffiliation::class;

    /**
     * @return array
     */
    public function definition(): array
    {
        return [
            'corporation_id' => fake()->numberBetween(98541680, 98541699),
            'alliance_id' => fake()->optional()->numberBetween(99000001, 99000049),
            'faction_id' => null,
        ];
    }
}
