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

namespace Seat\Eveapi\Jobs\Assets\Corporation;

use Seat\Eveapi\Jobs\AbstractAuthCorporationJob;
use Seat\Eveapi\Models\Assets\CorporationAsset;

/**
 * Class Names.
 *
 * @package Seat\Eveapi\Jobs\Assets\Corporation
 */
class Names extends AbstractAuthCorporationJob
{
    /**
     * @var string
     */
    protected $method = 'post';

    /**
     * @var string
     */
    protected $endpoint = '/corporations/{corporation_id}/assets/names/';

    /**
     * @var string
     */
    protected string $compatibility_date = '2025-07-20';

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
    protected $tags = ['corporation', 'asset'];

    /**
     * The maximum number of itemids we can request name
     * information for.
     *
     * @var int
     */
    protected $item_id_limit = 1000;

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

        // Get the assets for this character, chunked in a number of blocks
        // that the endpoint will accept.
        CorporationAsset::join('invTypes', 'type_id', '=', 'typeID')
            ->join('invGroups', 'invGroups.groupID', '=', 'invTypes.groupID')
            ->where('corporation_id', $this->getCorporationId())
            ->where('is_singleton', true)// only singleton items may be named
            // it seems only items from that categories can be named
            // 2  : Celestial
            // 6  : Ship
            // 22 : Deployable
            // 23 : Starbase
            // 46 : Orbitals
            // 65 : Structure
            ->whereIn('categoryID', [2, 6, 22, 23, 46, 65])// it seems only items from that categories can be named
            ->select('item_id')
            ->chunk($this->item_id_limit, function ($item_ids) {

                $this->request_body = $item_ids->pluck('item_id')->all();

                $response = $this->retrieve([
                    'corporation_id' => $this->getCorporationId(),
                ]);

                $names = collect($response->getBody());

                $names->each(function ($name) {

                    // "None" seems to indidate that no name is set.
                    if ($name->name === 'None')
                        return;

                    CorporationAsset::where('corporation_id', $this->getCorporationId())
                        ->where('item_id', $name->item_id)
                        ->update(['name' => $name->name]);
                });
            });
    }
}
