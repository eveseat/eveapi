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
 * Class Orders
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
    protected $version = 'v1';

    /**
     * Execute the job.
     *
     * @return void
     * @throws \Exception
     */
    public function handle()
    {

        $orders = $this->retrieve([
            'character_id' => $this->getCharacterId(),
        ]);

        collect($orders)->each(function ($order) {

            CharacterOrder::firstOrNew([
                'character_id' => $this->getCharacterId(),
                'order_id'     => $order->order_id,
            ])->fill([
                'type_id'       => $order->type_id,
                'region_id'     => $order->region_id,
                'location_id'   => $order->location_id,
                'range'         => $order->range,
                'is_buy_order'  => $order->is_buy_order,
                'price'         => $order->price,
                'volume_total'  => $order->volume_total,
                'volume_remain' => $order->volume_remain,
                'issued'        => carbon($order->issued),
                'state'         => $order->state,
                'min_volume'    => $order->min_volume,
                'account_id'    => $order->account_id,
                'duration'      => $order->duration,
                'is_corp'       => $order->is_corp,
                'escrow'        => $order->escrow,
            ])->save();
        });
    }
}
