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
 *  You should have received a copy of the GNU General Public License along
 *  with this program; if not, write to the Free Software Foundation, Inc.,
 *  51 Franklin Street, Fifth Floor, Boston, MA 02110-1301 USA.
 */

namespace Seat\Eveapi\Mapping\Sde;

use Illuminate\Database\Eloquent\Model;
use Seat\Eveapi\Models\Sde\DgmTypeAttribute;

/**
 * DgmTypeAttributeMapping.
 *
 * Used to import csv data into dgmTypeAttributes table.
 * CSV file must be formatted using Fuzzwork format.
 *
 * @url https://www.fuzzwork.co.uk
 */
class DgmTypeAttributeMapping extends AbstractFuzzworkMapping
{
    /**
     * @param array $row
     * @return Model|Model[]|null
     */
    public function model(array $row)
    {
        return (new DgmTypeAttribute([
            'typeID'      => $row[0],
            'attributeID' => $row[1],
            'valueInt'    => $row[2],
            'valueFloat'  => $row[3],
        ]))->bypassReadOnly();
    }
}
