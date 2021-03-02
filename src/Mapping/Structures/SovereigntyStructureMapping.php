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
 * Class SovereigntyStructureMapping.
 * @package Seat\Eveapi\Mapping\Structures
 */
class SovereigntyStructureMapping extends DataMapping
{
    /**
     * @var array
     */
    protected static $mapping = [
        'structure_type_id'             => 'structure_type_id',
        'alliance_id'                   => 'alliance_id',
        'solar_system_id'               => 'solar_system_id',
        'vulnerability_occupancy_level' => 'vulnerability_occupancy_level',
        'vulnerable_start_time'         => 'vulnerable_start_time',
        'vulnerable_end_time'           => 'vulnerable_end_time',
    ];
}
