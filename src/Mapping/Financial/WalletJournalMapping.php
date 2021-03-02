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

namespace Seat\Eveapi\Mapping\Financial;

use Seat\Eveapi\Mapping\DataMapping;

/**
 * Class WalletJournalMapping.
 * @package Seat\Eveapi\Mapping\Financial
 */
class WalletJournalMapping extends DataMapping
{
    /**
     * @var array
     */
    protected static $mapping = [
        'id'              => 'id',                         // changed from ref_id to id into v4
        'date'            => 'date',
        'ref_type'        => 'ref_type',
        'first_party_id'  => 'first_party_id',
        'second_party_id' => 'second_party_id',
        'amount'          => 'amount',
        'balance'         => 'balance',
        'reason'          => 'reason',
        'tax_receiver_id' => 'tax_receiver_id',
        'tax'             => 'tax',
        // appears in version 4
        'description'     => 'description',
        'context_id'      => 'context_id',
        'context_id_type' => 'context_id_type',
    ];
}
