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

namespace Seat\Eveapi\Mapping\Sde;

use Illuminate\Database\Eloquent\Model;
use Seat\Eveapi\Models\Sde\InvGroup;

/**
 * InvGroupMapping.
 *
 * Used to import csv data into invGroups table.
 * CSV file must be formatted using Fuzzwork format.
 *
 * @url https://www.fuzzwork.co.uk
 */
class InvGroupMapping extends AbstractFuzzworkMapping
{
    /**
     * @param  array  $row
     * @return Model|Model[]|null
     */
    public function model(array $row)
    {
        return (new InvGroup([
            'groupID'              => $row[0],
            'categoryID'           => $row[1],
            'groupName'            => $row[2],
            'iconID'               => $row[3],
            'useBasePrice'         => $row[4],
            'anchored'             => $row[5],
            'anchorable'           => $row[6],
            'fittableNonSingleton' => $row[7],
            'published'            => $row[8],
        ]))->bypassReadOnly();
    }
}
