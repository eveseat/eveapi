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

namespace Seat\Eveapi\Mapping\Structures;

use Seat\Eveapi\Mapping\DataMapping;

/**
 * Class CorporationStructureMapping.
 * @package Seat\Eveapi\Mapping\Structures
 */
class CorporationStructureMapping extends DataMapping
{
    /**
     * @var array
     */
    protected static $mapping = [
        'structure_id'           => 'structure_id',
        'corporation_id'         => 'corporation_id',
        'type_id'                => 'type_id',
        'system_id'              => 'system_id',
        'profile_id'             => 'profile_id',
        'fuel_expires'           => 'fuel_expires',
        'state_timer_start'      => 'state_timer_start',
        'state_timer_end'        => 'state_timer_end',
        'unanchors_at'           => 'unanchors_at',
        'state'                  => 'state',
        'reinforce_weekday'      => 'reinforce_weekday',
        'reinforce_hour'         => 'reinforce_hour',
        'next_reinforce_weekday' => 'next_reinforce_weekday',
        'next_reinforce_hour'    => 'next_reinforce_hour',
        'next_reinforce_apply'   => 'next_reinforce_apply',
    ];
}
