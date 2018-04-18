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

namespace Seat\Eveapi\Jobs\Universe;

use Seat\Eveapi\Jobs\EsiBase;
use Seat\Eveapi\Models\Universe\UniverseName;
use Seat\Eveapi\Models\Wallet\CharacterWalletJournal;

/**
 * Class Names.
 * @package Seat\Eveapi\Jobs\Universe
 */
class Names extends EsiBase
{

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
    protected $version = 'v2';

    /**
     * @var array
     */
    protected $tags = ['public', 'universe', 'names'];

    /**
     * The maximum number of entity ids we can request resolution for.
     */
    const ITEMS_ID_LIMIT = 1000;

    /**
     * @var \Illuminate\Support\Collection
     */
    protected $entity_ids;

    /**
     * Execute the job.
     *
     * @throws \Throwable
     */
    public function handle()
    {

        $this->entity_ids = collect();

        $this->entity_ids->push(CharacterWalletJournal::select('first_party_id')
                ->whereNotIn('first_party_id', UniverseName::select('entity_id')->distinct()->get())
                ->distinct()
                ->get()
                ->pluck('first_party_id')
                ->toArray());

        $this->entity_ids->push(CharacterWalletJournal::select('second_party_id')
                ->whereNotIn('second_party_id', UniverseName::select('entity_id')->distinct()->get())
                ->distinct()
                ->get()
                ->pluck('second_party_id')
                ->toArray());

        $this->entity_ids->flatten()->chunk(self::ITEMS_ID_LIMIT)->each(function ($chunk) {

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
