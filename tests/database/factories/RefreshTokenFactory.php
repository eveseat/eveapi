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
use Seat\Eveapi\Models\RefreshToken;

/**
 * Class RefreshTokenFactory.
 * @package Seat\Tests\Web\Database\Factories
 */
class RefreshTokenFactory extends Factory
{
    protected $model = RefreshToken::class;

    /**
     * @return array
     */
    public function definition(): array
    {
        return [
            'character_id' => fake()->numberBetween(98000000, 98001794),
            'version' => fake()->numberBetween(1, RefreshToken::CURRENT_VERSION),
            'user_id' => fake()->numberBetween(1, 10),
            'refresh_token' => fake()->sha256,
            'scopes' => [
                'esi-characters.read_agents_research.v1', 'esi-characters.read_blueprints.v1',
                'esi-characters.read_fatigue.v1', ' esi-characters.read_medals.v1', 'esi-characters.read_notifications.v1',
            ],
            'expires_on' => fake()->dateTime(),
            'token' => fake()->sha256,
            'character_owner_hash' => fake()->sha256,
        ];
    }
}
