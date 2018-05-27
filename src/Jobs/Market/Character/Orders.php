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

namespace Seat\Eveapi\Jobs\Market\Character;

use Seat\Eveapi\Jobs\EsiBase;
use Seat\Eveapi\Models\Market\CharacterOrder;

/**
 * Class Orders.
 * @package Seat\Eveapi\Jobs\Market\Character
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
    protected $endpoint = '/characters/{character_id}/orders/';

    /**
     * @var int
     */
    protected $version = 'v2';

    /**
     * @var string
     */
    protected $scope = 'esi-markets.read_character_orders.v1';

    /**
     * @var array
     */
    protected $tags = ['character', 'market', 'orders'];

    /**
     * Execute the job.
     *
     * @throws \Throwable
     */
    public function handle()
    {

        if (! $this->preflighted()) return;

        $orders = $this->retrieve([
            'character_id' => $this->getCharacterId(),
        ]);

        if ($orders->isCachedLoad()) return;

        collect($orders)->each(function ($order) {

            CharacterOrder::firstOrNew([
                'character_id' => $this->getCharacterId(),
                'order_id'     => $order->order_id,
            ])->fill([
                'type_id'        => $order->type_id,
                'region_id'      => $order->region_id,
                'location_id'    => $order->location_id,
                'range'          => $order->range,
                'is_buy_order'   => $order->is_buy_order ?? null,
                'price'          => $order->price,
                'volume_total'   => $order->volume_total,
                'volume_remain'  => $order->volume_remain,
                'issued'         => carbon($order->issued),
                'min_volume'     => $order->min_volume ?? null,
                'duration'       => $order->duration,
                'is_corporation' => $order->is_corporation,
                'escrow'         => $order->escrow ?? null,
            ])->save();
        });
    }
}
