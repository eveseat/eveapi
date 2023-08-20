<?php

/*
 * This file is part of SeAT
 *
 * Copyright (C) 2015 to present Leon Jacobs
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
 * Class WalletTransactionMapping.
 *
 * @package Seat\Eveapi\Mapping\Financial
 */
class WalletTransactionMapping extends DataMapping
{
    /**
     * @var string[]
     */
    protected static $mapping = [
        'transaction_id' => 'transaction_id',
        'date'           => 'date',
        'type_id'        => 'type_id',
        'location_id'    => 'location_id',
        'unit_price'     => 'unit_price',
        'quantity'       => 'quantity',
        'client_id'      => 'client_id',
        'is_buy'         => 'is_buy',
        'journal_ref_id' => 'journal_ref_id',
    ];
}
