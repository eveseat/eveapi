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

namespace Seat\Eveapi\Jobs\Industry\Character;

use Seat\Eveapi\Jobs\EsiBase;
use Seat\Eveapi\Models\Industry\CharacterIndustryJob;

/**
 * Class Jobs.
 * @package Seat\Eveapi\Jobs\Industry\Character
 */
class Jobs extends EsiBase
{
    /**
     * @var string
     */
    protected $method = 'get';

    /**
     * @var string
     */
    protected $endpoint = '/characters/{character_id}/industry/jobs/';

    /**
     * @var array
     */
    protected $query_string = [
        'include_completed' => true,
    ];

    /**
     * @var string
     */
    protected $version = 'v1';

    /**
     * @var string
     */
    protected $scope = 'esi-industry.read_character_jobs.v1';

    /**
     * @var array
     */
    protected $tags = ['character', 'industry', 'jobs'];

    /**
     * Execute the job.
     *
     * @throws \Throwable
     */
    public function handle()
    {

        if (! $this->preflighted()) return;

        $industry_jobs = $this->retrieve([
            'character_id' => $this->getCharacterId(),
        ]);

        if ($industry_jobs->isCachedLoad()) return;

        collect($industry_jobs)->each(function ($job) {

            CharacterIndustryJob::firstOrNew([
                'character_id' => $this->getCharacterId(),
                'job_id'       => $job->job_id,
            ])->fill([
                'installer_id'           => $job->installer_id,
                'facility_id'            => $job->facility_id,
                'station_id'             => $job->station_id,
                'activity_id'            => $job->activity_id,
                'blueprint_id'           => $job->blueprint_id,
                'blueprint_type_id'      => $job->blueprint_type_id,
                'blueprint_location_id'  => $job->blueprint_location_id,
                'output_location_id'     => $job->output_location_id,
                'runs'                   => $job->runs,
                'cost'                   => $job->cost ?? null,
                'licensed_runs'          => $job->licensed_runs ?? null,
                'probability'            => $job->probability ?? null,
                'product_type_id'        => $job->product_type_id ?? null,
                'status'                 => $job->status,
                'duration'               => $job->duration,
                'start_date'             => carbon($job->start_date),
                'end_date'               => carbon($job->end_date),
                'pause_date'             => property_exists($job, 'pause_date') ?
                    carbon($job->pause_date) : null,
                'completed_date'         => property_exists($job, 'completed_date') ?
                    carbon($job->completed_date) : null,
                'completed_character_id' => $job->completed_character_id ?? null,
                'successful_runs'        => $job->successful_runs ?? null,
            ])->save();
        });
    }
}
