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
use Seat\Eveapi\Mapping\Assets\AssetMapping;
use Seat\Eveapi\Models\Assets\CharacterAsset;
use Seat\Eveapi\Models\RefreshToken;

/**
 * Class Assets.
 *
 * @package Seat\Eveapi\Jobs\Assets\Character
 */
class Assets extends AbstractAuthCharacterJob
{
    /**
     * @var string
     */
    protected $method = 'get';

    /**
     * @var string
     */
    protected $endpoint = '/characters/{character_id}/assets/';

    /**
     * @var string
     */
    protected $version = 'v5';

    /**
     * @var string
     */
    protected $scope = 'esi-assets.read_assets.v1';

    /**
     * @var array
     */
    protected $tags = ['character', 'asset'];

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
     * @param  \Seat\Eveapi\Models\RefreshToken  $token
     */
    public function __construct(RefreshToken $token)
    {
        $this->known_assets = collect();

        parent::__construct($token);
    }

    /**
     * @return string
     */
    public function displayName(): string
    {
        return 'Retrieve character assets';
    }

    /**
     * Execute the job.
     *
     * @return void
     *
     * @throws \Throwable
     */
    public function handle(): void
    {
        parent::handle();

        while (true) {

            $response = $this->retrieve([
                'character_id' => $this->getCharacterId(),
            ]);

            if ($response->isFromCache() && CharacterAsset::where('character_id', $this->getCharacterId())->count() > 0)
                return;

            $assets = collect($response->getBody());

            $assets->each(function ($asset) {

                $model = CharacterAsset::firstOrNew([
                    'item_id' => $asset->item_id,
                ]);

                AssetMapping::make($model, $asset, [
                    'character_id' => function () {
                        return $this->getCharacterId();
                    },
                ])->save();

                // Update the list of known item_id's which should be
                // excluded from the database cleanup later.
                $this->known_assets->push($asset->item_id);
            });

            if (! $this->nextPage($response->getPagesCount()))
                break;
        }

        // Cleanup old assets
        CharacterAsset::where('character_id', $this->getCharacterId())
            ->whereNotIn('item_id', $this->known_assets->flatten()->all())
            ->delete();
    }
}
