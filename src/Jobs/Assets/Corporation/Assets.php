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

namespace Seat\Eveapi\Jobs\Assets\Corporation;

use Seat\Eveapi\Jobs\EsiBase;
use Seat\Eveapi\Models\Assets\CorporationAsset;
use Seat\Eveapi\Models\RefreshToken;

/**
 * Class Assets.
 * @package Seat\Eveapi\Jobs\Assets\Corporation
 */
class Assets extends EsiBase
{
    /**
     * @var string
     */
    protected $method = 'get';

    /**
     * @var string
     */
    protected $endpoint = '/corporations/{corporation_id}/assets/';

    /**
     * @var string
     */
    protected $version = 'v3';

    /**
     * @var string
     */
    protected $scope = 'esi-assets.read_corporation_assets.v1';

    /**
     * @var array
     */
    protected $roles = ['Director'];

    /**
     * @var array
     */
    protected $tags = ['corporation', 'assets'];

    /**
     * @var int
     */
    protected $page = 1;

    /**
     * @var \Illuminate\Support\Collection
     */
    protected $known_assets;

    /**
     * Assets constructor.
     *
     * @param \Seat\Eveapi\Models\RefreshToken|null $token
     */
    public function __construct(RefreshToken $token = null)
    {

        $this->known_assets = collect();

        parent::__construct($token);
    }

    /**
     * Execute the job.
     *
     * @return void
     * @throws \Throwable
     */
    public function handle(): void
    {

        if (! $this->preflighted()) return;

        while (true) {

            $assets = $this->retrieve([
                'corporation_id' => $this->getCorporationId(),
            ]);

            if ($assets->isCachedLoad()) return;

            collect($assets)->chunk(1000)->each(function ($chunk) {

                $records = $chunk->map(function ($asset, $key) {

                    return [
                        'item_id'        => $asset->item_id,
                        'corporation_id' => $this->getCorporationId(),
                        'type_id'        => $asset->type_id,
                        'quantity'       => $asset->quantity,
                        'location_id'    => $asset->location_id,
                        'location_type'  => $asset->location_type,
                        'location_flag'  => $asset->location_flag,
                        'is_singleton'   => $asset->is_singleton,
                        'created_at'     => carbon(),
                        'updated_at'     => carbon(),
                    ];
                });

                CorporationAsset::insertOnDuplicateKey($records->toArray(), [
                    'item_id',
                    'corporation_id',
                    'type_id',
                    'quantity',
                    'location_id',
                    'location_type',
                    'location_flag',
                    'is_singleton',
                    'updated_at',
                ]);
            });

            // Update the list of known item_id's which should be
            // excluded from the database cleanup later.
            $this->known_assets->push(collect($assets)
                ->pluck('item_id')->flatten()->all());

            if (! $this->nextPage($assets->pages))
                break;
        }

        // Cleanup old assets
        CorporationAsset::where('corporation_id', $this->getCorporationId())
            ->whereNotIn('item_id', $this->known_assets->flatten()->all())
            ->delete();
    }
}
