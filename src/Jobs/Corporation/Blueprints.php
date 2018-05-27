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

namespace Seat\Eveapi\Jobs\Corporation;

use Seat\Eveapi\Jobs\EsiBase;
use Seat\Eveapi\Models\Corporation\CorporationBlueprint;
use Seat\Eveapi\Models\RefreshToken;

/**
 * Class Blueprints.
 * @package Seat\Eveapi\Jobs\Corporation
 */
class Blueprints extends EsiBase
{

    /**
     * @var string
     */
    protected $method = 'get';

    /**
     * @var string
     */
    protected $endpoint = '/corporations/{corporation_id}/blueprints/';

    /**
     * @var string
     */
    protected $version = 'v2';

    /**
     * @var string
     */
    protected $scope = 'esi-corporations.read_blueprints.v1';

    /**
     * @var array
     */
    protected $roles = ['Director'];

    /**
     * @var array
     */
    protected $tags = ['corporation', 'blueprints'];

    /**
     * @var int
     */
    protected $page = 1;

    /**
     * @var \Illuminate\Support\Collection
     */
    protected $known_blueprints;

    /**
     * Blueprints constructor.
     *
     * @param \Seat\Eveapi\Models\RefreshToken|null $token
     */
    public function __construct(RefreshToken $token = null)
    {

        $this->known_blueprints = collect();

        parent::__construct($token);
    }

    /**
     * Execute the job.
     *
     * @return void
     * @throws \Throwable
     */
    public function handle()
    {

        if (! $this->preflighted()) return;

        while (true) {

            $blueprints = $this->retrieve([
                'corporation_id' => $this->getCorporationId(),
            ]);

            if ($blueprints->isCachedLoad()) return;

            collect($blueprints)->chunk(100)->each(function ($chunk) {

                $records = $chunk->map(function ($blueprint, $key) {
                    return [
                        'corporation_id' => $this->getCorporationId(),
                        'item_id'        => $blueprint->item_id,
                        'type_id'             => $blueprint->type_id,
                        'location_id'         => $blueprint->location_id,
                        'location_flag'       => $blueprint->location_flag,
                        'quantity'            => $blueprint->quantity,
                        'time_efficiency'     => $blueprint->time_efficiency,
                        'material_efficiency' => $blueprint->material_efficiency,
                        'runs'                => $blueprint->runs,
                        'created_at'          => carbon(),
                        'updated_at'          => carbon(),
                    ];
                });

                CorporationBlueprint::insertOnDuplicateKey($records->toArray(), [
                    'corporation_id',
                    'item_id',
                    'type_id',
                    'location_id',
                    'location_flag',
                    'quantity',
                    'time_efficiency',
                    'material_efficiency',
                    'runs',
                    'updated_at',
                ]);

            });

            $this->known_blueprints->push(collect($blueprints)
                ->pluck('item_id')->flatten()->all());

            if (! $this->nextPage($blueprints->pages))
                break;

        }

        // Cleanup lost blueprints
        CorporationBlueprint::where('corporation_id', $this->getCorporationId())
            ->whereNotIn('item_id', $this->known_blueprints->flatten()->all())
            ->delete();
    }
}
