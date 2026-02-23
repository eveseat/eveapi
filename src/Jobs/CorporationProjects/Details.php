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

namespace Seat\Eveapi\Jobs\CorporationProjects;

use Carbon\Carbon;
use Seat\Eveapi\Jobs\AbstractAuthCorporationJob;
use Seat\Eveapi\Mapping\CorporationProjects\ProjectsMapping;
use Seat\Eveapi\Models\CorporationProjects\CorporationProject;
use Seat\Eveapi\Models\RefreshToken;

/**
 * Class Projects.
 *
 * @package Seat\Eveapi\Jobs\CorporationProjects
 */
class Details extends AbstractAuthCorporationJob
{
    /**
     * @var string
     */
    protected $method = 'get';

    /**
     * @var string
     */
    protected $endpoint = '/corporations/{corporation_id}/projects/{project_id}';

    /**
     * @var string
     */
    protected $scope = 'esi-corporations.read_projects.v1';

    /**
     * When this job was written, so ESI can try to serve a response compatible with the behaviour of the endpoint at that time.
     *
     * @var string
     */
    protected string $compatibility_date = '2025-12-16';

    /**
     * @var array
     */
    protected $tags = ['corporation', 'project'];

    /**
     * @var string
     */
    private $project_id;

    /**
     * @var string
     */
    protected $version = '';

    public function __construct(int $corporation_id, RefreshToken $token, string $project_id)
    {
        $this->project_id = $project_id;

        parent::__construct($corporation_id, $token);
    }

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

        $cid = $this->getCorporationId(); // Extract it here early as we use it a log

        $response = $this->retrieve([
            'project_id' => $this->project_id,
            'corporation_id' => $cid,
        ]);

        $details = $response->getBody();

        $lm = Carbon::parse($details->last_modified);

        // Weird early projects, bad data
        $thresholdDate = Carbon::parse('2025-01-01');
        if ($lm->isBefore($thresholdDate)){
            logger()->warning('early project detected', ['body' => $details]); // TODO investigate

            return;
        }

        $proj = CorporationProject::firstOrNew([
            'id' => $this->project_id,
            'corporation_id' => $cid,
        ]);

        ProjectsMapping::make($proj, $details, [
            'last_modified' => function () use ($lm) {
                return $lm->format('Y-m-d H:i:s');
            },
            'corporation_id' => function () use ($cid) {
                return $cid;
            },
            'created' => function () use ($details) {
                return Carbon::parse($details->details->created)->format('Y-m-d H:i:s');
            },
            'finished' => function () use ($details) {
                if (! isset($details->details->finished)){
                    return;
                }

                return Carbon::parse($details->details->finished)->format('Y-m-d H:i:s');
            },
            'expires' => function () use ($details) {
                if (! isset($details->details->expires)){
                    return;
                }

                return Carbon::parse($details->details->expires)->format('Y-m-d H:i:s');
            },
            'configuration' => function () use ($details) {
                return json_encode($details->configuration);
            },
        ])->save();

    }
}
