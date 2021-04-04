<?php

/*
 * This file is part of SeAT
 *
 * Copyright (C) 2015 to 2020 Leon Jacobs
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

namespace Seat\Eveapi\Jobs\Universe;

use Seat\Eveapi\Jobs\EsiBase;
use Seat\Eveapi\Models\Universe\UniverseName;
use Seat\Eveapi\Models\Wallet\CharacterWalletJournal;
use Seat\Eveapi\Models\Wallet\CharacterWalletTransaction;

/**
 * Class Names.
 * @package Seat\Eveapi\Jobs\Universe
 */
class Names extends EsiBase
{

    /**
     * The maximum number of entity ids we can request resolution for.
     *
     * @var int
     */
    const ITEMS_LIMIT = 1000;

    /**
     * @var string
     */
    protected $method = 'post';

    /**
     * @var string
     */
    protected $endpoint = '/universe/names/';

    /**
     * @var string
     */
    protected $version = 'v3';

    /**
     * @var array
     */
    protected $tags = ['public', 'universe'];

    /**
     * @var \Illuminate\Support\Collection
     */
    protected $entity_ids;

    /**
     * @var \Illuminate\Support\Collection
     */
    protected $existing_entity_ids;

    /**
     * Names constructor.
     * @param array $entity_ids
     */
    public function __construct(array $entity_ids)
    {
        $this->entity_ids = collect($entity_ids);
    }

    /**
     * Execute the job.
     *
     * @throws \Throwable
     */
    public function handle()
    {

        $this->existing_entity_ids = UniverseName::select('entity_id')
            ->distinct()
            ->get()
            ->pluck('entity_id');

        $this->entity_ids->flatten()->diff($this->existing_entity_ids)->values()->chunk(self::ITEMS_LIMIT)->each(function ($chunk) {

            $this->request_body = collect($chunk->values()->all())->unique()->values()->all();

            $resolutions = $this->retrieve();

            collect($resolutions)->each(function ($resolution) {

                UniverseName::firstOrNew([
                    'entity_id' => $resolution->id,
                ])->fill([
                    'name'     => $resolution->name,
                    'category' => $resolution->category,
                ])->save();

            });

        });
    }
}
