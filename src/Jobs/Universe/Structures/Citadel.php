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

namespace Seat\Eveapi\Jobs\Universe\Structures;

use Seat\Eseye\Exceptions\RequestFailedException;
use Seat\Eveapi\Contracts\CitadelAccessCache;
use Seat\Eveapi\Jobs\AbstractAuthCharacterJob;
use Seat\Eveapi\Mapping\Structures\UniverseStructureMapping;
use Seat\Eveapi\Models\RefreshToken;
use Seat\Eveapi\Models\Universe\UniverseStructure;

/**
 * Class Citadels.
 *
 * @package Seat\Eveapi\Jobs\Universe
 */
class Citadel extends AbstractAuthCharacterJob
{
    /**
     * HTTP 403 is frequent for Citadel jobs. Decrease RATE_LIMIT to not starve out other jobs.
     */
    const RATE_LIMIT = parent::RATE_LIMIT * 0.65;

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

    private int $structure_id;

    /**
     * @param  int  $structure_id
     * @param  RefreshToken  $token
     */
    public function __construct(int $structure_id, RefreshToken $token)
    {
        parent::__construct($token);
        $this->structure_id = $structure_id;
    }

    /**
     * {@inheritdoc}
     */
    public function tags(): array
    {
        $tags = parent::tags();

        $tags[] = $this->structure_id;

        return $tags;
    }

    /**
     * {@inheritdoc}
     *
     * @throws RequestFailedException
     */
    public function handle()
    {
        parent::handle();

        // it looks like we've loaded it in the meantime
        if(UniverseStructure::find($this->structure_id) !== null) return;

        // check if the acl cache allows refetching the structure
        $accessCache = app()->make(CitadelAccessCache::class);
        if(! $accessCache::canAccess($this->getCharacterId(), $this->structure_id)) return;

        try {
            // attempt to resolve the structure
            $response = $this->retrieve([
                'structure_id' => $this->structure_id,
            ]);

            $model = UniverseStructure::firstOrNew([
                'structure_id' => $this->structure_id,
            ]);

            $structure = $response->getBody();

            UniverseStructureMapping::make($model, $structure, [
                'structure_id' => function () {
                    return $this->structure_id;
                },
            ])->save();

        } catch (RequestFailedException $e) {
            if($e->getEsiResponse()->getErrorCode() === 403) {
                $accessCache::blockAccess($this->getCharacterId(), $this->structure_id);
            } else {
                throw $e;
            }
        }

    }

    /**
     * @param  \Throwable  $exception
     * 
     * We have a unique case here where we want to delete the failed job in
     * the case that its a MaxAttemptsExceededException as we dont want to
     * keep polluting the failed_jobs
     *
     * @throws \Exception
     */
    public function failed(Throwable $exception)
    {
        // used token is non longer valid, remove it from the system.
        if ($exception instanceof MaxAttemptsExceededException) {
            logger()->warning(
                sprintf('[Citadel] Citadel job failed due to MaxAttemptsExceeded, deleting.'),
                [
                    'structure_id' => $this->structure_id,
                    'character_id' => $this->getCharacterId(),
                ]);
            $this->delete();
            return;
        }

        parent::failed($exception);
    }
}
