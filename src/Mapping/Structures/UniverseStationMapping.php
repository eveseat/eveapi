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
 * Class UniverseStationMapping.
 * @package Seat\Eveapi\Mapping\Structures
 */
class UniverseStationMapping extends DataMapping
{
    /**
     * @var array
     */
    protected static $mapping = [
        'type_id'                    => 'type_id',
        'name'                       => 'name',
        'owner'                      => 'owner',
        'race_id'                    => 'race_id',
        'x'                          => 'position.x',
        'y'                          => 'position.y',
        'z'                          => 'position.z',
        'system_id'                  => 'system_id',
        'reprocessing_efficiency'    => 'reprocessing_efficiency',
        'reprocessing_stations_take' => 'reprocessing_stations_take',
        'max_dockable_ship_volume'   => 'max_dockable_ship_volume',
        'office_rental_cost'         => 'office_rental_cost',
    ];
}
