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
use Seat\Eveapi\Models\Sde\DgmTypeAttribute;

/**
 * DgmTypeAttributeMapping.
 *
 * Used to import csv data into dgmTypeAttributes table.
 * CSV file must be formatted using Fuzzwork format.
 *
 * @url https://www.fuzzwork.co.uk
 */
class DgmTypeAttributeMapping extends AbstractFuzzworkMapping implements WithValidation
{
    /**
     * @param  array  $row
     * @return Model|Model[]|null
     */
    public function model(array $row)
    {
        return (new DgmTypeAttribute([
            'typeID' => $row[0],
            'attributeID' => $row[1],
            'valueInt' => $row[2],
            'valueFloat' => $row[3],
        ]))->bypassReadOnly();
    }

    /**
     * @return array
     */
    public function rules(): array
    {
        return [
            '0' => 'integer|min:1|required',
            '1' => 'integer|min:1|required',
            '2' => 'integer|nullable',
            '3' => 'numeric|nullable',
        ];
    }

    public function customValidationAttributes()
    {
        return [
            '0' => 'typeID',
            '1' => 'attributeID',
            '2' => 'valueInt',
            '3' => 'valueFloat',
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
            '2.integer' => self::INTEGER_VALIDATION_MESSAGE,
            '3.numeric' => self::NUMERIC_VALIDATION_MESSAGE,
        ];
    }
}
