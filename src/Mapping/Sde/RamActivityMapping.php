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
use Maatwebsite\Excel\Concerns\WithValidation;
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
class RamActivityMapping extends AbstractFuzzworkMapping implements WithValidation
{
    /**
     * @param  \PhpOffice\PhpSpreadsheet\Cell\Cell  $cell
     * @param  $value
     * @return bool
     *
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
     * @param  array  $row
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

    /**
     * @return array
     */
    public function rules(): array
    {
        return [
            '0' => 'integer|min:0|required',
            '1' => 'string|max:250|required',
            '2' => 'string|max:10|nullable',
            '3' => 'string|max:250|required',
            '4' => 'boolean|required',
        ];
    }

    public function customValidationAttributes()
    {
        return [
            '0' => 'activityID',
            '1' => 'activityName',
            '2' => 'iconNo',
            '3' => 'description',
            '4' => 'published',
        ];
    }

    public function customValidationMessages()
    {
        return [
            '0.integer' => self::INTEGER_VALIDATION_MESSAGE,
            '0.min' => self::MIN_VALIDATION_MESSAGE,
            '0.required' => self::REQUIRED_VALIDATION_MESSAGE,
            '1.string' => self::STRING_VALIDATION_MESSAGE,
            '1.max' => self::MAX_VALIDATION_MESSAGE,
            '1.required' => self::REQUIRED_VALIDATION_MESSAGE,
            '2.string' => self::STRING_VALIDATION_MESSAGE,
            '2.max' => self::MAX_VALIDATION_MESSAGE,
            '3.string' => self::STRING_VALIDATION_MESSAGE,
            '3.max' => self::MAX_VALIDATION_MESSAGE,
            '3.required' => self::REQUIRED_VALIDATION_MESSAGE,
            '4.boolean' => self::BOOLEAN_VALIDATION_MESSAGE,
            '4.required' => self::REQUIRED_VALIDATION_MESSAGE,
        ];
    }
}
