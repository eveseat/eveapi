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

namespace Seat\Eveapi\Mapping\Financial;

use Seat\Eveapi\Mapping\DataMapping;

/**
 * Class OrderMapping.
 *
 * @package Seat\Eveapi\Mapping\Financial
 */
class OrderMapping extends DataMapping
{
    /**
     * @var array
     */
    protected static $mapping = [
        'order_id'        => 'order_id',
        'type_id'         => 'type_id',
        'region_id'       => 'region_id',
        'location_id'     => 'location_id',
        'range'           => 'range',
        'is_buy_order'    => 'is_buy_order',
        'price'           => 'price',
        'volume_total'    => 'volume_total',
        'volume_remain'   => 'volume_remain',
        'issued'          => 'issued',
        'min_volume'      => 'min_volume',
        'duration'        => 'duration',
        'escrow'          => 'escrow',
    ];
}
