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
use Seat\Eveapi\Models\Sde\InvMarketGroup;

/**
 * InvMarketGroupMapping.
 *
 * Used to import csv data into invMarketGroups table.
 * CSV file must be formatted using Fuzzwork format.
 *
 * @url https://www.fuzzwork.co.uk
 */
class InvMarketGroupMapping extends AbstractFuzzworkMapping implements WithValidation
{
    /**
     * @param  array  $row
     * @return Model|Model[]|null
     */
    public function model(array $row)
    {
        return (new InvMarketGroup([
            'marketGroupID'   => $row[0],
            'parentGroupID'   => $row[1],
            'marketGroupName' => $row[2],
            'description'     => $row[3],
            'iconID'          => $row[4],
        ]))->bypassReadOnly();
    }

    /**
     * @return array
     */
    public function rules(): array
    {
        return [
            '0' => 'integer|min:1|required',
            '1' => 'integer|min:1|nullable',
            '2' => 'string|max:100|required',
            '3' => 'string|max:250|nullable',
            '4' => 'integer|min:0|nullable',
        ];
    }

    public function customValidationAttributes()
    {
        return [
            '0' => 'marketGroupID',
            '1' => 'parentGroupID',
            '2' => 'marketGroupName',
            '3' => 'description',
            '4' => 'iconID',
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
            '2.string' => self::STRING_VALIDATION_MESSAGE,
            '2.max' => self::MAX_VALIDATION_MESSAGE,
            '2.required' => self::REQUIRED_VALIDATION_MESSAGE,
            '3.string' => self::STRING_VALIDATION_MESSAGE,
            '3.max' => self::MAX_VALIDATION_MESSAGE,
            '4.integer' => self::INTEGER_VALIDATION_MESSAGE,
            '4.min' => self::MIN_VALIDATION_MESSAGE,
            '4.required' => self::REQUIRED_VALIDATION_MESSAGE,
        ];
    }
}
