<?php

/*
 * This file is part of SeAT
 *
 * Copyright (C) 2015, 2016, 2017, 2018  Leon Jacobs
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

namespace Seat\Eveapi\Jobs\Market\Corporation;

use Seat\Eveapi\Jobs\EsiBase;
use Seat\Eveapi\Models\Market\CorporationOrder;

/**
 * Class Orders.
 * @package Seat\Eveapi\Jobs\Market\Corporation
 */
class Orders extends EsiBase
{
    /**
     * @var string
     */
    protected $method = 'get';

    /**
     * @var string
     */
    protected $endpoint = '/corporations/{corporation_id}/orders/';

    /**
     * @var int
     */
    protected $version = 'v2';

    /**
     * @var string
     */
    protected $scope = 'esi-markets.read_corporation_orders.v1';

    /**
     * @var array
     */
    protected $roles = ['Accountant', 'Trader'];

    /**
     * @var array
     */
    protected $tags = ['corporations', 'market', 'orders'];

    /**
     * @var int
     */
    protected $page = 1;

    /**
     * Execute the job.
     *
     * @throws \Throwable
     */
    public function handle()
    {

        if (! $this->preflighted()) return;

        while (true) {

            $orders = $this->retrieve([
                'corporation_id' => $this->getCorporationId(),
            ]);

            if ($orders->isCachedLoad()) return;

            collect($orders)->each(function ($order) {

                CorporationOrder::firstOrNew([
                    'corporation_id' => $this->getCorporationId(),
                    'order_id'       => $order->order_id,
                ])->fill([
                    'type_id'         => $order->type_id,
                    'region_id'       => $order->region_id,
                    'location_id'     => $order->location_id,
                    'range'           => $order->range,
                    'is_buy_order'    => $order->is_buy_order ?? null,
                    'price'           => $order->price,
                    'volume_total'    => $order->volume_total,
                    'volume_remain'   => $order->volume_remain,
                    'issued'          => carbon($order->issued),
                    'min_volume'      => $order->min_volume ?? null,
                    'wallet_division' => $order->wallet_division ?? null,
                    'duration'        => $order->duration ?? null,
                    'escrow'          => $order->escrow ?? null,
                ])->save();
            });

            if (! $this->nextPage($orders->pages))
                return;
        }
    }
}
