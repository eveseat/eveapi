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

use Seat\Eveapi\Mapping\DataMapping;

use Closure;
use Flow\JSONPath\JSONPath;
use Flow\JSONPath\JSONPathException;

/**
 * AbstractFuzzworkMapping.
 *
 * Used to import csv data into SDE tables.
 * 
 *
 * @url https://www.fuzzwork.co.uk
 */
abstract class AbstractSdeMapping extends DataMapping
{

    protected const MULTI_ARRAY_KEY = ["_key", "theModelKey"];

    protected const MULTI_NEST_PATH = "";

    public static function detail(array $model, $data, array $overrides = []): array
    {
        // merge both mapping and overrides to build final mapping rules
        $rules = array_merge(static::$mapping, $overrides);

        // prepare a JSON Query navigator
        $json_path = new JSONPath($data);

        // loop over rules and apply data mapping
        foreach ($rules as $model_field => $source_field) {

            // if source field is a closure, apply its business logic
            if ($source_field instanceof Closure) {
                $model[$model_field] = $source_field();
            } else {
                try {
                    $model[$model_field] = $json_path->find($source_field)->first();
                } catch (JSONPathException $e) {
                    $model[$model_field] = null;

                    logger()->error($e->getMessage(), $e->getTrace());
                }
            }
        }

        return $model;
    }

    //  Made for eg typeDogma, where multiple lines are nested as an array under the main key
    public static function multiDetail(array $models, $data, array $overrides = []): array
    {
        // prepare a JSON Query navigator
        $json_path = new JSONPath($data);
        $key = $json_path->find(static::MULTI_ARRAY_KEY[0])->first();
        $nestPotential = $json_path->find(static::MULTI_NEST_PATH)->first();
        if (is_null($nestPotential)) {
            // Nothing to do for this multi, continue
            return $models;
        }
        $nest = $nestPotential->getData();

        foreach ($nest as $detail) {
            $models[] = static::detail([], $detail, [static::MULTI_ARRAY_KEY[1] => function () use ($key) {
                return $key;
            }]);
        }

        return $models;
    }
}
