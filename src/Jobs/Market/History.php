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

namespace Seat\Eveapi\Jobs\Market;

use Illuminate\Support\Facades\Redis;
use Seat\Eseye\Exceptions\RequestFailedException;
use Seat\Eveapi\Jobs\EsiBase;
use Seat\Eveapi\Models\Market\Price;

/**
 * Class History.
 *
 * @package Seat\Eveapi\Jobs\Market
 */
class History extends EsiBase
{
    const THE_FORGE = 10000002;

    // override the default from AbstractJob
    public const JOB_EXECUTION_TIMEOUT = 60 * 60 * 24; //1 day

    /**
     * Describes how long the rate limit window lasts in seconds before resetting.
     *
     * @var int
     */
    const ENDPOINT_RATE_LIMIT_WINDOW = 60; // to be on the safe side, we set it to 61 rather than 60

    /**
     * Describes how many calls can be made in the timespan described in ENDPOINT_RATE_LIMIT_WINDOW.
     *
     * @var int
     */
    const ENDPOINT_RATE_LIMIT_CALLS = 280;

    /**
     * @var string
     */
    protected $method = 'get';

    /**
     * @var string
     */
    protected $endpoint = '/markets/{region_id}/history/';

    /**
     * @var string
     */
    protected $version = 'v1';

    /**
     * @var array
     */
    protected $tags = ['public', 'market'];

    /**
     * @var array
     */
    private $type_ids;

    /**
     * History constructor.
     *
     * @param  array  $type_ids
     */
    public function __construct(array $type_ids)
    {
        parent::__construct();

        $this->type_ids = $type_ids;
    }

    /**
     * {@inheritdoc}
     *
     * @throws \Seat\Services\Exceptions\SettingException
     * @throws \Throwable
     */
    public function handle()
    {
        $region_id = setting('market_prices_region_id', true) ?: self::THE_FORGE;

        while(count($this->type_ids) > 0) {
            // don't go quite to the limit, maybe ccp_round() is involved somewhere along
            Redis::throttle('market-history-throttle')->allow(self::ENDPOINT_RATE_LIMIT_CALLS)->every(self::ENDPOINT_RATE_LIMIT_WINDOW)->then(function () use ($region_id) {
                $type_id = array_shift($this->type_ids);

                $this->query_string = [
                    'type_id' => $type_id,
                ];

                try {
                    // for each subsequent item, request ESI order stats using region in settings (The Forge is default).
                    $response = $this->retrieve([
                        'region_id' => $region_id,
                    ]);
                  
                    $prices = $response->getBody();

                    // search the more recent entry in returned history.
                    $price = collect($prices)->where('order_count', '>', 0)
                        ->sortByDesc('date')
                        ->first();

                    if (is_null($price)) {
                        $price = (object) [
                            'average'     => 0.0,
                            'highest'     => 0.0,
                            'lowest'      => 0.0,
                            'order_count' => 0,
                            'volume'      => 0,
                        ];
                    }

                    Price::updateOrCreate([
                        'type_id' => $type_id,
                    ], [
                        'average'     => $price->average,
                        'highest'     => $price->highest,
                        'lowest'      => $price->lowest,
                        'order_count' => $price->order_count,
                        'volume'      => $price->volume,
                    ]);
                } catch (RequestFailedException $e) {
                    logger()->error($e->getMessage());
                }
            }, function () {
                // Could not obtain lock...
                return $this->release(10);
            });
        }
    }
}
