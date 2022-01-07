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
use Seat\Eveapi\Models\Sde\StaStation;

/**
 * StaStationMapping.
 *
 * Used to import csv data into staStations table.
 * CSV file must be formatted using Fuzzwork format.
 *
 * @url https://www.fuzzwork.co.uk
 */
class StaStationMapping extends AbstractFuzzworkMapping implements WithValidation
{
    /**
     * @param  array  $row
     * @return Model|Model[]|null
     */
    public function model(array $row)
    {
        return (new StaStation([
            'stationID'                => $row[0],
            'security'                 => $row[1],
            'dockingCostPerVolume'     => $row[2],
            'maxShipVolumeDockable'    => $row[3],
            'officeRentalCost'         => $row[4],
            'operationID'              => $row[5],
            'stationTypeID'            => $row[6],
            'corporationID'            => $row[7],
            'solarSystemID'            => $row[8],
            'constellationID'          => $row[9],
            'regionID'                 => $row[10],
            'stationName'              => $row[11],
            'x'                        => $row[12],
            'y'                        => $row[13],
            'z'                        => $row[14],
            'reprocessingEfficiency'   => $row[15],
            'reprocessingStationsTake' => $row[16],
            'reprocessingHangarFlag'   => $row[17],
        ]))->bypassReadOnly();
    }

    /**
     * @return array
     */
    public function rules(): array
    {
        return [
            '0' => 'integer|between:60000000,64000000|required',
            '1' => 'numeric|between:-1,1|required',
            '2' => 'numeric|required',
            '3' => 'numeric|min:1|required',
            '4' => 'numeric|min:1|required',
            '5' => 'integer|min:1|required',
            '6' => 'integer|min:1|required',
            '7' => 'integer|between:1000000,2000000|required',
            '8' => 'integer|between:30000000,33000000|required',
            '9' => 'integer|between:20000000,23000000|required',
            '10' => 'integer|between:10000000,13000000|required',
            '11' => 'string|max:100|required',
            '12' => 'numeric|required',
            '13' => 'numeric|required',
            '14' => 'numeric|required',
            '15' => 'numeric|between:0,1|required',
            '16' => 'numeric|between:0,1|required',
            '17' => 'integer|min:1|required',
        ];
    }

    public function customValidationAttributes()
    {
        return [
            '0' => 'stationID',
            '1' => 'security',
            '2' => 'dockingCostPerVolume',
            '3' => 'maxShipVolumeDockable',
            '4' => 'officeRentalCost',
            '5' => 'operationID',
            '6' => 'stationTypeID',
            '7' => 'corporationID',
            '8' => 'solarSystemID',
            '9' => 'constellationID',
            '10' => 'regionID',
            '11' => 'stationName',
            '12' => 'x',
            '13' => 'y',
            '14' => 'z',
            '15' => 'reprocessingEfficiency',
            '16' => 'reprocessingStationsTake',
            '17' => 'reprocessingHangarFlag',
        ];
    }

    public function customValidationMessages()
    {
        return [
            '0.integer' => self::INTEGER_VALIDATION_MESSAGE,
            '0.between' => self::BETWEEN_VALIDATION_MESSAGE,
            '0.required' => self::REQUIRED_VALIDATION_MESSAGE,
            '1.numeric' => self::NUMERIC_VALIDATION_MESSAGE,
            '1.between' => self::BETWEEN_VALIDATION_MESSAGE,
            '1.required' => self::REQUIRED_VALIDATION_MESSAGE,
            '2.numeric' => self::NUMERIC_VALIDATION_MESSAGE,
            '2.required' => self::REQUIRED_VALIDATION_MESSAGE,
            '3.numeric' => self::NUMERIC_VALIDATION_MESSAGE,
            '3.min' => self::MIN_VALIDATION_MESSAGE,
            '3.required' => self::REQUIRED_VALIDATION_MESSAGE,
            '4.numeric' => self::NUMERIC_VALIDATION_MESSAGE,
            '4.min' => self::MIN_VALIDATION_MESSAGE,
            '4.required' => self::REQUIRED_VALIDATION_MESSAGE,
            '5.integer' => self::INTEGER_VALIDATION_MESSAGE,
            '5.min' => self::MIN_VALIDATION_MESSAGE,
            '5.required' => self::REQUIRED_VALIDATION_MESSAGE,
            '6.integer' => self::INTEGER_VALIDATION_MESSAGE,
            '6.min' => self::MIN_VALIDATION_MESSAGE,
            '6.required' => self::REQUIRED_VALIDATION_MESSAGE,
            '7.integer' => self::INTEGER_VALIDATION_MESSAGE,
            '7.between' => self::BETWEEN_VALIDATION_MESSAGE,
            '7.required' => self::REQUIRED_VALIDATION_MESSAGE,
            '8.integer' => self::INTEGER_VALIDATION_MESSAGE,
            '8.between' => self::BETWEEN_VALIDATION_MESSAGE,
            '8.required' => self::REQUIRED_VALIDATION_MESSAGE,
            '9.integer' => self::INTEGER_VALIDATION_MESSAGE,
            '9.between' => self::BETWEEN_VALIDATION_MESSAGE,
            '9.required' => self::REQUIRED_VALIDATION_MESSAGE,
            '10.integer' => self::INTEGER_VALIDATION_MESSAGE,
            '10.between' => self::BETWEEN_VALIDATION_MESSAGE,
            '10.required' => self::REQUIRED_VALIDATION_MESSAGE,
            '11.string' => self::STRING_VALIDATION_MESSAGE,
            '11.max' => self::MAX_VALIDATION_MESSAGE,
            '11.required' => self::REQUIRED_VALIDATION_MESSAGE,
            '12.numeric' => self::NUMERIC_VALIDATION_MESSAGE,
            '12.required' => self::REQUIRED_VALIDATION_MESSAGE,
            '13.numeric' => self::NUMERIC_VALIDATION_MESSAGE,
            '13.required' => self::REQUIRED_VALIDATION_MESSAGE,
            '14.numeric' => self::NUMERIC_VALIDATION_MESSAGE,
            '14.required' => self::REQUIRED_VALIDATION_MESSAGE,
            '15.numeric' => self::NUMERIC_VALIDATION_MESSAGE,
            '15.between' => self::BETWEEN_VALIDATION_MESSAGE,
            '15.required' => self::REQUIRED_VALIDATION_MESSAGE,
            '16.numeric' => self::NUMERIC_VALIDATION_MESSAGE,
            '16.between' => self::BETWEEN_VALIDATION_MESSAGE,
            '16.required' => self::REQUIRED_VALIDATION_MESSAGE,
            '17.integer' => self::INTEGER_VALIDATION_MESSAGE,
            '17.min' => self::MIN_VALIDATION_MESSAGE,
            '17.required' => self::REQUIRED_VALIDATION_MESSAGE,
        ];
    }
}
