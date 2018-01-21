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

namespace Seat\Eveapi\Jobs\Industry\Corporation\Mining;


use Seat\Eveapi\Jobs\EsiBase;
use Seat\Eveapi\Models\Industry\CorporationIndustryMiningExtraction;

/**
 * Class MiningExtractions
 * @package Seat\Eveapi\Jobs\Industry\Corporation
 */
class Extractions extends EsiBase
{
    /**
     * @var string
     */
    protected $method = 'get';

    /**
     * @var string
     */
    protected $endpoint = '/corporation/{corporation_id}/mining/extractions/';

    /**
     * @var string
     */
    protected $version = 'v1';

    /**
     * Execute the job.
     *
     * @return void
     * @throws \Exception
     */
    public function handle()
    {

        $mining_extractions = $this->retrieve([
            'corporation_id' => $this->getCorporationId(),
        ]);

        collect($mining_extractions)->each(function ($extraction) {

            CorporationIndustryMiningExtraction::firstOrNew([
                'corporation_id' => $this->getCorporationId(),
                'structure_id'   => $extraction->structure_id,
                'extraction_start_time' => carbon($extraction->extraction_start_time),
            ])->fill([
                'moon_id'               => $extraction->moon_id,
                'chunk_arrival_time'    => carbon($extraction->chunk_arrival_time),
                'natural_decay_time'    => carbon($extraction->natural_decay_time),
            ])->save();
        });

    }
}
