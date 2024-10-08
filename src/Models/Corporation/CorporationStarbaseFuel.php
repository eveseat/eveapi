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

namespace Seat\Eveapi\Models\Corporation;

use Seat\Eveapi\Traits\HasCompositePrimaryKey;
use Seat\Services\Contracts\HasTypeID;
use Seat\Services\Models\ExtensibleModel;

/**
 * Class CorporationStarbaseFuel.
 *
 * @package Seat\Eveapi\Models\Corporation
 */
class CorporationStarbaseFuel extends ExtensibleModel implements HasTypeID
{

    use HasCompositePrimaryKey;

    /**
     * @var bool
     */
    protected static $unguarded = true;

    /**
     * @var array
     */
    protected $primaryKey = ['corporation_id', 'starbase_id', 'type_id'];

    /**
     * @return int The eve type id of this object
     */
    public function getTypeID(): int
    {
        return $this->type_id;
    }
}
