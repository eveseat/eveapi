<?php

/*
 * This file is part of SeAT
 *
 * Copyright (C) 2015, 2016, 2017, 2018, 2019  Leon Jacobs
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

/**
 * Interface Structures.
 *
 * @package Seat\Eveapi\Jobs\Universe
 */
interface IStructures
{
    const BUGGED_ASSETS_RANGE = [40000000, 50000000];

    const ASSET_SAFETY = 2004;

    const SOLAR_SYSTEMS_RANGE = [30000000, 33000000];

    const NPC_STATIONS_RANGE = [60000000, 64000000];

    const START_UPWELL_RANGE = 100000000;

    /**
     * Return a list of Structure ID which have to be resolved by the job.
     *
     * @return int[]
     */
    public function getStructuresIdToResolve(): array;
}
