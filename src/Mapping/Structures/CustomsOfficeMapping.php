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

namespace Seat\Eveapi\Mapping\Structures;

use Seat\Eveapi\Mapping\DataMapping;

/**
 * Class CustomsOfficeMapping.
 *
 * @package Seat\Eveapi\Mapping\Structures
 */
class CustomsOfficeMapping extends DataMapping
{
    /**
     * @var string[]
     */
    protected static $mapping = [
        'office_id'                   => 'office_id',
        'system_id'                   => 'system_id',
        'reinforce_exit_start'        => 'reinforce_exit_start',
        'reinforce_exit_end'          => 'reinforce_exit_end',
        'corporation_tax_rate'        => 'corporation_tax_rate',
        'allow_alliance_access'       => 'allow_alliance_access',
        'alliance_tax_rate'           => 'alliance_tax_rate',
        'allow_access_with_standings' => 'allow_access_with_standings',
        'standing_level'              => 'standing_level',
        'excellent_standing_tax_rate' => 'excellent_standing_tax_rate',
        'good_standing_tax_rate'      => 'good_standing_tax_rate',
        'neutral_standing_tax_rate'   => 'neutral_standing_tax_rate',
        'bad_standing_tax_rate'       => 'bad_standing_tax_rate',
        'terrible_standing_tax_rate'  => 'terrible_standing_tax_rate',
    ];
}
