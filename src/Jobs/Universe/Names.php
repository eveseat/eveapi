<?php

/*
 * This file is part of SeAT
 *
 * Copyright (C) 2015 to present Leon Jacobs
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

use Illuminate\Support\Collection;
use Seat\Eveapi\Jobs\EsiBase;
use Seat\Eveapi\Models\Universe\UniverseName;
use Seat\Eveapi\Models\Wallet\CharacterWalletJournal;
use Seat\Eveapi\Models\Wallet\CharacterWalletTransaction;

/**
 * Class Names.
 *
 * @package Seat\Eveapi\Jobs\Universe
 */
class Names extends EsiBase
{

    /**
     * The maximum number of entity ids we can request resolution for.
     */
    protected $items_id_limit = 1000;

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
     * @param \Illuminate\Support\Collection|null $entity_ids
     * @throws \Illuminate\Contracts\Container\BindingResolutionException
     */
    public function __construct (?Collection $entity_ids = null)
    {
        parent::__construct();

        if ($entity_ids) {
            $this->entity_ids = $entity_ids;
        }
    }

    /**
     * Execute the job.
     *
     * @throws \Throwable
     */
    public function handle()
    {

        // if no entity IDs were specified, try to resolve all unresolved universe names
        if (!isset($this->entity_ids)) {
            $this->entity_ids->push(CharacterWalletJournal::select('first_party_id')
                ->whereNotNull('first_party_id')
                ->distinct()
                ->get()
                ->pluck('first_party_id')
                ->toArray());

            $this->entity_ids->push(CharacterWalletJournal::select('second_party_id')
                ->whereNotNull('second_party_id')
                ->distinct()
                ->get()
                ->pluck('second_party_id')
                ->toArray());

            $this->entity_ids->push(CharacterWalletTransaction::select('client_id')
                ->whereNotNull('client_id')
                ->distinct()
                ->get()
                ->pluck('client_id')
                ->toArray());
        }

        if ($this->entity_ids->isEmpty()) {
            return;
        }

        $this->existing_entity_ids = UniverseName::select('entity_id')
            ->distinct()
            ->get()
            ->pluck('entity_id');

        $this->entity_ids->flatten()->diff($this->existing_entity_ids)->values()->chunk($this->items_id_limit)->each(function ($chunk) {

            $this->request_body = collect($chunk->values()->all())->unique()->values()->all();

            $response = $this->retrieve();

            $resolutions = $response->getBody();

            collect($resolutions)->each(function ($resolution) {

                UniverseName::firstOrNew([
                    'entity_id' => $resolution->id,
                ])->fill([
                    'name' => $resolution->name,
                    'category' => $resolution->category,
                ])->save();

            });

        });
    }
}
