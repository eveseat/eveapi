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

namespace Seat\Eveapi\Jobs\Sovereignty;

use Seat\Eveapi\Jobs\EsiBase;
use Seat\Eveapi\Models\Sovereignty\SovereigntyStructure;

/**
 * Class Structures.
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
    protected $tags = ['public', 'sovereignty', 'structures'];

    /**
     * Execute the job.
     *
     * @throws \Throwable
     */
    public function handle()
    {

        if (! $this->preflighted()) return;

        $structures = $this->retrieve();

        if ($structures->isCachedLoad()) return;

        collect($structures)->each(function ($structure) {

            SovereigntyStructure::firstOrNew([
                'structure_id' => $structure->structure_id,
            ])->fill([
                'structure_type_id'             => $structure->structure_type_id,
                'alliance_id'                   => $structure->alliance_id,
                'solar_system_id'               => $structure->solar_system_id,
                'vulnerability_occupancy_level' => $structure->vulnerability_occupancy_level ?? null,
                'vulnerable_start_time'         => property_exists($structure, 'vulnerable_start_time') ?
                    carbon($structure->vulnerable_start_time) : null,
                'vulnerable_end_time'           => property_exists($structure, 'vulnerable_end_time') ?
                    carbon($structure->vulnerable_end_time) : null,
            ])->save();

        });

        SovereigntyStructure::whereNotIn('structure_id', collect($structures)
            ->pluck('structure_id')->flatten()->all())
            ->delete();
    }
}
