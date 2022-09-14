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

namespace Seat\Eveapi\Jobs\Sovereignty;

use Seat\Eveapi\Jobs\EsiBase;
use Seat\Eveapi\Mapping\Structures\SovereigntyStructureMapping;
use Seat\Eveapi\Models\Sovereignty\SovereigntyStructure;

/**
 * Class Structures.
 *
 * @package Seat\Eveapi\Jobs\Sovereignty
 */
class Structures extends EsiBase
{
    /**
     * @var string
     */
    protected $method = 'get';

    /**
     * @var string
     */
    protected $endpoint = '/sovereignty/structures/';

    /**
     * @var string
     */
    protected $version = 'v1';

    /**
     * @var array
     */
    protected $tags = ['sovereignty'];

    /**
     * @return string
     */
    public function displayName(): string
    {
        return 'Discover universe structures...';
    }

    /**
     * Execute the job.
     *
     * @throws \Throwable
     */
    public function handle()
    {
        $response = $this->retrieve();

        if ($response->isFromCache() && SovereigntyStructure::count() > 0) return;

        $structures = $response->getBody();

        collect($structures)->each(function ($structure) {

            $model = SovereigntyStructure::firstOrNew([
                'structure_id' => $structure->structure_id,
            ]);

            SovereigntyStructureMapping::make($model, $structure, [
                'structure_id' => function () use ($structure) {
                    return $structure->structure_id;
                },
            ])->save();

        });

        SovereigntyStructure::whereNotIn('structure_id', collect($structures)
            ->pluck('structure_id')->flatten()->all())
            ->delete();
    }
}
