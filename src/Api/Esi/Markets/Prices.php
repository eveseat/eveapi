<?php

/*
 * This file is part of SeAT
 *
 * Copyright (C) 2017  Loic Leuilliot
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

namespace Seat\Eveapi\Api\Esi\Markets;

use Illuminate\Support\Facades\Cache;
use Seat\Eseye\Configuration;
use Seat\Eseye\Eseye;
use Seat\Eveapi\Models\Esi\Markets\Price;

class Prices
{
    public function call()
    {
        // We're using Redis in order to avoid to overflow database
        // and reduce job time
        //
        // Redis server is automatically dropping expired key
        // Therefore, in order to ensure that a response is still valid
        // We only need to check its existence
        if (Cache::has('esi:markets:prices'))
            return;

        $configuration = Configuration::getInstance();
        $configuration->datasource = 'tranquility';
        $configuration->file_cache_location = storage_path('app/eseye');
        $configuration->logfile_location = storage_path('logs/eseye.log');

        $prices = (new Eseye())->setVersion('v1')
            ->invoke('get', '/markets/prices/');

        // Create a flag key with the expiration time set as the one returned by ESI
        Cache::put('esi:markets:prices', 1, $prices->expires());

        // Iterate over all returned prices, update existing records and create new ones
        foreach ($prices as $price)
        {
            // since all parameters are not mandatory
            // ensure they exist first and use a default value
            $adjusted_price = 0.0;
            $average_price = 0.0;

            if (property_exists($price, 'adjusted_price'))
                $adjusted_price = $price->adjusted_price;

            if (property_exists($price, 'average_price'))
                $average_price = $price->average_price;

            Price::updateOrCreate(
                [
                    'type_id' => $price->type_id,
                ],
                [
                    'adjusted_price' => $adjusted_price,
                    'average_price' => $average_price,
                ]
            );
        }
    }
}
