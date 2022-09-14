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

namespace Seat\Eveapi\Jobs\Assets\Character;

use Seat\Eveapi\Jobs\AbstractAuthCharacterJob;
use Seat\Eveapi\Models\Assets\CharacterAsset;

/**
 * Class Names.
 *
 * @package Seat\Eveapi\Jobs\Assets\Character
 */
class Names extends AbstractAuthCharacterJob
{
    const CELESTIAL_CATEGORY = 2;
    const SHIP_CATEGORY = 6;
    const DEPLOYABLE_CATEGORY = 22;
    const STARBASE_CATEGORY = 23;
    const ORBITALS_CATEGORY = 46;
    const STRUCTURE_CATEGORY = 65;

    /**
     * @var string
     */
    protected $method = 'post';

    /**
     * @var string
     */
    protected $endpoint = '/characters/{character_id}/assets/names/';

    /**
     * @var string
     */
    protected $version = 'v1';

    /**
     * @var string
     */
    protected $scope = 'esi-assets.read_assets.v1';

    /**
     * @var array
     */
    protected $tags = ['character', 'asset'];

    /**
     * The maximum number of itemids we can request name
     * information for.
     *
     * @var int
     */
    protected $item_id_limit = 1000;

    /**
     * @return string
     */
    public function displayName(): string
    {
        return 'Retrieve character assets name';
    }

    /**
     * Execute the job.
     *
     * @return void
     *
     * @throws \Exception
     */
    public function handle()
    {
        parent::handle();

        // Get the assets for this character, chunked in a number of blocks
        // that the endpoint will accept.
        CharacterAsset::join('invTypes', 'type_id', '=', 'typeID')
            ->join('invGroups', 'invGroups.groupID', '=', 'invTypes.groupID')
            ->where('character_id', $this->getCharacterId())
            ->where('is_singleton', true) // only singleton items may be named
            ->whereIn('categoryID', [ // It seems like only items from these categories can be named
                self::CELESTIAL_CATEGORY, self::SHIP_CATEGORY, self::DEPLOYABLE_CATEGORY,
                self::STARBASE_CATEGORY, self::ORBITALS_CATEGORY, self::STRUCTURE_CATEGORY,
            ])
            ->select('item_id')
            ->chunk($this->item_id_limit, function ($item_ids) {

                $this->request_body = $item_ids->pluck('item_id')->all();

                $response = $this->retrieve([
                    'character_id' => $this->getCharacterId(),
                ]);

                $names = collect($response->getBody());

                $names->each(function ($name) {

                    // "None" seems to indicate that no name is set.
                    if ($name->name === 'None')
                        return;

                    CharacterAsset::where('character_id', $this->getCharacterId())
                        ->where('item_id', $name->item_id)
                        ->update(['name' => $name->name]);
                });
            });
    }
}
