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
use Illuminate\Support\Facades\Bus;
use Seat\Eveapi\Jobs\AbstractAuthCorporationJob;
use Seat\Eveapi\Mapping\CorporationProjects\ProjectsMapping;
use Seat\Eveapi\Models\CorporationProjects\CorporationProject;

/**
 * Class Projects.
 *
 * @package Seat\Eveapi\Jobs\CorporationProjects
 */
class Projects extends AbstractAuthCorporationJob
{
    /**
     * @var string
     */
    protected $method = 'get';

    /**
     * @var string
     */
    protected $endpoint = '/corporations/{corporation_id}/projects';

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
    protected $version = '';

    /**
     * @var \Illuminate\Support\Collection
     */
    private $project_jobs;

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

        $this->query_string['limit'] = '100';
        $this->query_string['state'] = 'All';

        $before = '0';

        $this->project_jobs = collect();

        while (true) {

            // TODO - proper cursor based caching, not just grab it all every time.
            $this->query_string['before'] = $before;

            $response = $this->retrieve([
                'corporation_id' => $this->getCorporationId(),
            ]);

            $projects = $response->getBody();
            if (isset($projects->cursor) && isset($projects->cursor->before)) {
                $before = $projects->cursor->before;
            } else {
                break; // empty cursor
            }
            if (isset($projects->projects)) {
                foreach ($projects->projects as $project) {
                    $lm = Carbon::parse($project->last_modified);
                    // Weird early projects
                    $thresholdDate = Carbon::parse('2025-01-01');
                    if ($lm->isBefore($thresholdDate)){
                        logger()->warning('early project detected', ['project' => $project]); // // These may need investigation by CCP if requried.
                        continue;
                    }

                    $proj = CorporationProject::firstOrNew([
                        'id' => $project->id,
                    ]);

                    ProjectsMapping::make($proj, $project, [
                        'last_modified' => function () use ($lm) {
                            return $lm->format('Y-m-d H:i:s');
                        },
                        'corporation_id' => function () {
                            return $this->getCorporationId();
                        },
                    ])->save();

                    $this->project_jobs->add(new Details($this->getCorporationId(), $this->getToken(), $proj->id));
                    $this->project_jobs->add(new Contributors($this->getCorporationId(), $this->getToken(), $proj->id));
                }
            } else {
                // We have reached the end of the dataset
                break;
            }
        }

        if ($this->project_jobs->isNotEmpty()) {
            if($this->batchId) {
                $this->batch()->add($this->project_jobs->toArray());
            } else {
                Bus::batch($this->project_jobs->toArray())
                    ->name(sprintf('Projects: %s', $this->token->character->name ?? $this->token->character_id))
                    ->onQueue($this->job->getQueue())
                    ->dispatch();
            }
        }

    }
}
