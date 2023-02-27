<?php

/*
 * This file is part of SeAT
 *
 * Copyright (C) 2015 to 2023 Leon Jacobs
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

namespace Seat\Eveapi\Jobs\Market;

use Seat\Eveapi\Jobs\AbstractJob;
use Seat\Eveapi\Models\Market\MarketOrder;
use Illuminate\Support\Facades\DB;
use Seat\Eveapi\Models\Market\Price;

/**
 * Class OrderAggregates.
 *
 * @package Seat\Eveapi\Jobs\Market
 */
class OrderAggregates extends AbstractJob
{
    protected $tags = ["market","orders"];

    public function handle()
    {
        $now = carbon();

        MarketOrder::selectRaw("type_id, MIN(price) as sell_price")
            ->groupBy("type_id")
            ->where("is_buy_order",false)
            ->chunk(500, function ($types) use ($now) {
                $types = $types->map(function ($type) use ($now) {
                    return [
                        "type_id"=>$type->type_id,
                        "sell_price"=>$type->sell_price,
                        'created_at'     => $now,
                        'updated_at'     => $now,
                    ];
                });

                Price::upsert($types->toArray(),
                    [
                        "type_id",
                        "sell_price",
                    ]
                );
            });

        MarketOrder::selectRaw("type_id, MAX(price) as buy_price")
            ->groupBy("type_id")
            ->where("is_buy_order",true)
            ->chunk(500, function ($types) use ($now) {
                $types = $types->map(function ($type) use ($now) {
                    return [
                        "type_id"=>$type->type_id,
                        "buy_price"=>$type->buy_price,
                        'created_at'     => $now,
                        'updated_at'     => $now,
                    ];
                });

                Price::upsert($types->toArray(),
                    [
                        "type_id",
                        "buy_price",
                        'updated_at',
                    ]
                );
            });
    }
}
