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

namespace Seat\Eveapi\Mapping\Corporations;

use Seat\Eveapi\Mapping\DataMapping;

/**
 * Class InfoMapping.
 *
 * @package Seat\Eveapi\Mapping\Corporations
 */
class InfoMapping extends DataMapping
{
    /**
     * @var array
     */
    protected static $mapping = [
        'name'            => 'name',
        'ticker'          => 'ticker',
        'member_count'    => 'member_count',
        'ceo_id'          => 'ceo_id',
        'alliance_id'     => 'alliance_id',
        'description'     => 'description',
        'tax_rate'        => 'tax_rate',
        'date_founded'    => 'date_founded',
        'creator_id'      => 'creator_id',
        'url'             => 'url',
        'faction_id'      => 'faction_id',
        'home_station_id' => 'home_station_id',
        'shares'          => 'shares',
    ];
}
