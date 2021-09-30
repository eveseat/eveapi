<?php

/*
 * This file is part of SeAT
 *
 * Copyright (C) 2015 to 2021 Leon Jacobs
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

namespace Seat\Eveapi\Mapping\Industry;

use Seat\Eveapi\Mapping\DataMapping;

/**
 * Class JobMapping.
 *
 * @package Seat\Eveapi\Mapping\Industry
 */
class JobMapping extends DataMapping
{
    /**
     * @var array
     */
    protected static $mapping = [
        'job_id'                 => 'job_id',
        'installer_id'           => 'installer_id',
        'facility_id'            => 'facility_id',
        'activity_id'            => 'activity_id',
        'blueprint_id'           => 'blueprint_id',
        'blueprint_type_id'      => 'blueprint_type_id',
        'blueprint_location_id'  => 'blueprint_location_id',
        'output_location_id'     => 'output_location_id',
        'runs'                   => 'runs',
        'cost'                   => 'cost',
        'licensed_runs'          => 'licensed_runs',
        'probability'            => 'probability',
        'product_type_id'        => 'product_type_id',
        'status'                 => 'status',
        'duration'               => 'duration',
        'start_date'             => 'start_date',
        'end_date'               => 'end_date',
        'pause_date'             => 'pause_date',
        'completed_date'         => 'completed_date',
        'completed_character_id' => 'completed_character_id',
        'successful_runs'        => 'successful_runs',
    ];
}
