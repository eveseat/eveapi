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

namespace Seat\Eveapi\Mapping\Sde\Ccp;

use Seat\Eveapi\Mapping\Sde\AbstractSdeMapping;

/**
 * DgmTypeAttributeMapping.
 *
 * Used to import csv data into dgmTypeAttributes table.
 * CSV file must be formatted using Fuzzwork format.
 *
 * @url https://www.fuzzwork.co.uk
 */
class InvControlTowerResourceMapping extends AbstractSdeMapping
{

    protected const MULTI_ARRAY_KEY = ["_key", "controlTowerTypeID"];

    protected const MULTI_NEST_PATH = "resources";

    /**
     * @var string[]
     */
    protected static $mapping = [
        'resourceTypeID' => 'resourceTypeID',
        'purpose' => 'purpose',
        'quantity' => 'quantity',
        'minSecurityLevel' => 'minSecurityLevel',
        'factionID' => 'factionID',
    ];
}
