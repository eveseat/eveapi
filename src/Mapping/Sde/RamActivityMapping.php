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
use PhpOffice\PhpSpreadsheet\Cell\Cell;
use PhpOffice\PhpSpreadsheet\Cell\DataType;
use Seat\Eveapi\Models\Sde\RamActivity;

/**
 * RamActivityMapping.
 *
 * Used to import csv data into ramActivities table.
 * CSV file must be formatted using Fuzzwork format.
 *
 * @url https://www.fuzzwork.co.uk
 */
class RamActivityMapping extends AbstractFuzzworkMapping
{
    /**
     * @param \PhpOffice\PhpSpreadsheet\Cell\Cell $cell
     * @param $value
     * @return bool
     * @throws \PhpOffice\PhpSpreadsheet\Exception
     */
    public function bindValue(Cell $cell, $value)
    {
        if ($cell->getColumn() == 'B' && $value == 'None') {
            $cell->setValueExplicit('None', DataType::TYPE_STRING);

            return true;
        }

        return parent::bindValue($cell, $value);
    }

    /**
     * @param array $row
     * @return Model|Model[]|null
     */
    public function model(array $row)
    {
        return (new RamActivity([
            'activityID'   => $row[0],
            'activityName' => $row[1],
            'iconNo'       => $row[2],
            'description'  => $row[3],
            'published'    => $row[4],
        ]))->bypassReadOnly();
    }
}
