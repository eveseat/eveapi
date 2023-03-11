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

namespace Seat\Eveapi\Jobs\Market\Character;

use Seat\Eveapi\Jobs\AbstractAuthCharacterJob;
use Seat\Eveapi\Jobs\Universe\Structures\StructureBatch;
use Seat\Eveapi\Mapping\Financial\OrderMapping;
use Seat\Eveapi\Models\Market\CharacterOrder;

/**
 * Class Orders.
 *
 * @package Seat\Eveapi\Jobs\Market\Character
 */
class Orders extends AbstractAuthCharacterJob
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
    protected $tags = ['character', 'market'];

    /**
     * Execute the job.
     *
     * @throws \Throwable
     */
    public function handle()
    {
        parent::handle();

        $response = $this->retrieve([
            'character_id' => $this->getCharacterId(),
        ]);

        if ($response->isFromCache() &&
            CharacterOrder::where('character_id', $this->getCharacterId())->count() > 0)
            return;

        $orders = $response->getBody();

        $structure_batch = new StructureBatch();

        collect($orders)->each(function ($order) use ($structure_batch) {
            $structure_batch->addStructure($order->location_id,$this->getCharacterId());

            $model = CharacterOrder::firstOrNew([
                'character_id' => $this->getCharacterId(),
                'order_id'     => $order->order_id,
            ]);

            OrderMapping::make($model, $order, [
                'character_id' => function () {
                    return $this->getCharacterId();
                },
                'is_corporation' => function () use ($order) {
                    return $order->is_corporation;
                },
            ])->save();
        });

        $structure_batch->submitJobs();
    }
}
