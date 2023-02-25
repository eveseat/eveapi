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

use Seat\Eveapi\Jobs\EsiBase;
use Seat\Eveapi\Models\Market\MarketOrder;
use Seat\Eveapi\Models\Market\Price;
use Illuminate\Support\Facades\DB;

/**
 * Class Prices.
 *
 * @package Seat\Eveapi\Jobs\Market
 */
class Orders extends EsiBase
{
    const THE_FORGE = 10000002;

    /**
     * @var string
     */
    protected $method = 'get';

    /**
     * @var string
     */
    protected $endpoint = '/markets/{region_id}/orders/';

    /**
     * @var string
     */
    protected $version = 'v1';

    /**
     * @var array
     */
    protected $tags = ['public', 'market'];

    /**
     * Execute the job.
     *
     * @throws \Throwable
     */
    public function handle()
    {
        // this job need a ton of memory. temporarily unlock memory limit as a hack fix
        ini_set('memory_limit', -1);

        $count = MarketOrder::count();
        $now = carbon();

        while (true) {
            $orders = $this->retrieve(["region_id" => setting('market_prices_region_id', true) ?: self::THE_FORGE]);

            if ($orders->isCachedLoad() && $count > 0) return;

            collect($orders)->chunk(1000)->each(function ($chunk) use ($now) {
                $records = $chunk->map(function ($order) use ($now) {
                    return [
                        'order_id' => $order->order_id,
                        'duration' => $order->duration,
                        'is_buy_order' => $order->is_buy_order,
                        'issued' => carbon($order->issued),
                        'location_id' => $order->location_id,
                        'min_volume' => $order->min_volume,
                        'price' => $order->price,
                        'range' => $order->range,
                        'system_id' => $order->system_id,
                        'type_id' => $order->type_id,
                        'volume_remaining' => $order->volume_remain,
                        'volume_total' => $order->volume_total,
                        'updated_at'=>$now,
                        'created_at'=>$now
                    ];
                });

                MarketOrder::upsert($records->toArray(), [
                    'order_id',
                ]);
            });

            if (! $this->nextPage($orders->pages)) break;
        }

        MarketOrder::whereRaw("ADDDATE(issued,INTERVAL duration DAY) < CURRENT_DATE()")->delete();
    }
}
