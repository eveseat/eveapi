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

namespace Seat\Eveapi\Mapping\Sde;

use Illuminate\Database\Eloquent\Model;
use Maatwebsite\Excel\Concerns\WithValidation;
use Seat\Eveapi\Models\Sde\InvGroup;

/**
 * InvGroupMapping.
 *
 * Used to import csv data into invGroups table.
 * CSV file must be formatted using Fuzzwork format.
 *
 * @url https://www.fuzzwork.co.uk
 */
class InvGroupMapping extends AbstractFuzzworkMapping implements WithValidation
{
    /**
     * @param  array  $row
     * @return Model|Model[]|null
     */
    public function model(array $row)
    {
        return (new InvGroup([
            'groupID' => $row[0],
            'categoryID' => $row[1],
            'groupName' => $row[2],
            'iconID' => $row[3],
            'useBasePrice' => $row[4],
            'anchored' => $row[5],
            'anchorable' => $row[6],
            'fittableNonSingleton' => $row[7],
            'published' => $row[8],
        ]))->bypassReadOnly();
    }

    /**
     * @return array
     */
    public function rules(): array
    {
        return [
            '0' => 'integer|min:0|required',
            '1' => 'integer|min:0|required',
            '2' => 'string|max:100|required',
            '3' => 'integer|min:0|nullable',
            '4' => 'boolean|required',
            '5' => 'boolean|required',
            '6' => 'boolean|required',
            '7' => 'boolean|required',
            '8' => 'boolean|required',
        ];
    }

    public function customValidationAttributes()
    {
        return [
            '0' => 'groupID',
            '1' => 'categoryID',
            '2' => 'groupName',
            '3' => 'iconID',
            '4' => 'useBasePrice',
            '5' => 'anchored',
            '6' => 'anchorable',
            '7' => 'fittableNonSingleton',
            '8' => 'published',
        ];
    }

    public function customValidationMessages()
    {
        return [
            '0.integer' => self::INTEGER_VALIDATION_MESSAGE,
            '0.min' => self::MIN_VALIDATION_MESSAGE,
            '0.required' => self::REQUIRED_VALIDATION_MESSAGE,
            '1.integer' => self::INTEGER_VALIDATION_MESSAGE,
            '1.min' => self::MIN_VALIDATION_MESSAGE,
            '1.required' => self::REQUIRED_VALIDATION_MESSAGE,
            '2.string' => self::STRING_VALIDATION_MESSAGE,
            '2.max' => self::MAX_VALIDATION_MESSAGE,
            '2.required' => self::REQUIRED_VALIDATION_MESSAGE,
            '3.integer' => self::INTEGER_VALIDATION_MESSAGE,
            '3.min' => self::MIN_VALIDATION_MESSAGE,
            '4.boolean' => self::BOOLEAN_VALIDATION_MESSAGE,
            '4.required' => self::REQUIRED_VALIDATION_MESSAGE,
            '6.boolean' => self::BOOLEAN_VALIDATION_MESSAGE,
            '6.required' => self::REQUIRED_VALIDATION_MESSAGE,
            '5.boolean' => self::BOOLEAN_VALIDATION_MESSAGE,
            '5.required' => self::REQUIRED_VALIDATION_MESSAGE,
            '7.boolean' => self::BOOLEAN_VALIDATION_MESSAGE,
            '7.required' => self::REQUIRED_VALIDATION_MESSAGE,
            '8.boolean' => self::BOOLEAN_VALIDATION_MESSAGE,
            '8.required' => self::REQUIRED_VALIDATION_MESSAGE,
        ];
    }
}
