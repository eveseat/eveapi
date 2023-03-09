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

namespace Seat\Eveapi\Jobs\Universe\Structures;

use Seat\Eseye\Exceptions\RequestFailedException;
use Seat\Eveapi\Jobs\AbstractAuthCharacterJob;
use Seat\Eveapi\Mapping\Structures\UniverseStructureMapping;
use Seat\Eveapi\Models\Assets\CharacterAsset;
use Seat\Eveapi\Models\RefreshToken;
use Seat\Eveapi\Models\Universe\UniverseStructure;

/**
 * Class Citadels.
 *
 * @package Seat\Eveapi\Jobs\Universe
 */
class Citadels extends AbstractAuthCharacterJob
{
    /**
     * @var string
     */
    protected $method = 'get';

    /**
     * @var string
     */
    protected $endpoint = '/universe/structures/{structure_id}/';

    /**
     * @var string
     */
    protected $scope = 'esi-universe.read_structures.v1';

    /**
     * @var string
     */
    protected $version = 'v2';

    /**
     * @var array
     */
    protected $tags = ['universe', 'structure'];

    private iterable $structure_ids;

    /**
     * @param iterable $structure_ids
     */
    public function __construct(iterable $structure_ids, RefreshToken $token)
    {
        parent::__construct($token);
        $this->structure_ids = $structure_ids;
    }

    /**
     * {@inheritdoc}
     */
    public function handle()
    {
        parent::handle();

        foreach ($this->structure_ids as $structure_id) {
            try {
                // attempt to resolve the structure
                $response = $this->retrieve([
                    'structure_id' => $structure_id,
                ]);

                $model = UniverseStructure::firstOrNew([
                    'structure_id' => $structure_id,
                ]);

                $structure = $response->getBody();

                UniverseStructureMapping::make($model, $structure, [
                    'structure_id' => function () use ($structure_id) {
                        return $structure_id;
                    },
                ])->save();

            } catch (RequestFailedException $e) {
                logger()->error('Unable to retrieve structure information.', [
                    'structure ID'   => $structure_id,
                    'token owner ID' => $this->getCharacterId(),
                ]);
            }
        }
    }
}
