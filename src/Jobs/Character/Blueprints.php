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

namespace Seat\Eveapi\Jobs\Character;


use Seat\Eveapi\Jobs\EsiBase;
use Seat\Eveapi\Models\Character\CharacterBluePrint;
use Seat\Eveapi\Models\RefreshToken;

/**
 * Class Blueprints
 * @package Seat\Eveapi\Jobs\Character
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
    protected $endpoint = '/characters/{character_id}/blueprints/';

    /**
     * @var int
     */
    protected $version = 'v2';

    /**
     * @var string
     */
    protected $scope = 'esi-characters.read_blueprints.v1';

    /**
     * @var array
     */
    protected $tags = ['character', 'blueprints'];

    /**
     * @var int
     */
    protected $page = 1;

    /**
     * Record of ID's that should be removed from this jobs
     * Model.
     *
     * @var \Illuminate\Support\Collection
     */
    protected $cleanup_ids;

    /**
     * Blueprints constructor.
     *
     * @param \Seat\Eveapi\Models\RefreshToken|null $token
     */
    public function __construct(RefreshToken $token = null)
    {

        $this->cleanup_ids = collect();

        parent::__construct($token);
    }

    /**
     * Execute the job.
     *
     * @return void
     * @throws \Exception
     * @throws \Throwable
     */
    public function handle()
    {

        // Start an infinite loop for the paged requests.
        while (true) {

            $blueprints = $this->retrieve([
                'character_id' => $this->getCharacterId(),
            ]);

            // Process the blueprints from the response
            collect($blueprints)->each(function ($blueprint) {

                CharacterBluePrint::firstOrNew([
                    'character_id' => $this->getCharacterId(),
                    'item_id'      => $blueprint->item_id,
                ])->fill([
                    'type_id'             => $blueprint->type_id,
                    'location_flag'       => $blueprint->location_flag,
                    'quantity'            => $blueprint->quantity,
                    'time_efficiency'     => $blueprint->time_efficiency,
                    'material_efficiency' => $blueprint->material_efficiency,
                    'runs'                => $blueprint->runs,
                ])->save();

            });

            // Add item ID's we should remove from the database.
            $this->cleanup_ids->push(collect($blueprints)->pluck('item_id'));

            // Check for pages left.
            if (! $this->nextPage($blueprints->pages))
                break;
        }

        $this->cleanup();
    }

    /**
     * Removes older entries from blueprints table.
     *
     * @throws \Exception
     */
    private function cleanup(): void
    {

        CharacterBluePrint::where('character_id', $this->getCharacterId())
            ->whereNotIn('item_id', $this->cleanup_ids->flatten()->unique()->all())
            ->delete();
    }
}
