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

namespace Seat\Eveapi\Jobs\Market\Corporation;

use Seat\Eveapi\Jobs\AbstractAuthCorporationJob;
use Seat\Eveapi\Mapping\Financial\OrderMapping;
use Seat\Eveapi\Models\Market\CorporationOrder;

/**
 * Class Orders.
 *
 * @package Seat\Eveapi\Jobs\Market\Corporation
 */
class Orders extends AbstractAuthCorporationJob
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
    protected $version = 'v3';

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
    protected $tags = ['corporation', 'market'];

    /**
     * @var int
     */
    protected $page = 1;

    /**
     * Execute the job.
     *
     * @return void
     *
     * @throws \Throwable
     */
    public function handle()
    {
        parent::handle();

        while (true) {

            $orders = $this->retrieve([
                'corporation_id' => $this->getCorporationId(),
            ]);

            if ($orders->isCachedLoad() &&
                CorporationOrder::where('corporation_id', $this->getCorporationId())->count() > 0)
                return;

            collect($orders)->each(function ($order) {

                $model = CorporationOrder::firstOrNew([
                    'corporation_id' => $this->getCorporationId(),
                    'order_id'       => $order->order_id,
                ]);

                OrderMapping::make($model, $order, [
                    'corporation_id' => function () {
                        return $this->getCorporationId();
                    },
                    'wallet_division' => function () use ($order) {
                        return $order->wallet_division;
                    },
                    'issued_by' => function () use ($order) {
                        return $order->issued_by;
                    },
                ])->save();
            });

            if (! $this->nextPage($orders->pages))
                return;
        }
    }
}
