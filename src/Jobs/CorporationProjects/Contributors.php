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

use Seat\Eveapi\Jobs\AbstractAuthCorporationJob;
use Seat\Eveapi\Models\CorporationProjects\CorporationProject;
use Seat\Eveapi\Models\CorporationProjects\CorporationProjectContributor;
use Seat\Eveapi\Models\RefreshToken;

/**
 * Class Projects.
 *
 * @package Seat\Eveapi\Jobs\CorporationProjects
 */
class Contributors extends AbstractAuthCorporationJob
{
    /**
     * @var string
     */
    protected $method = 'get';

    /**
     * @var string
     */
    protected $endpoint = '/corporations/{corporation_id}/projects/{project_id}/contributors';

    /**
     * @var string
     */
    protected $scope = 'esi-corporations.read_projects.v1';

    /**
     * @var array
     */
    protected $roles = ['Project Manager']; // TODO: TBC

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

    private $project_id;

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

        $this->query_string['limit'] = '100';

        $before = '0';

        $proj = CorporationProject::findOrFail($this->project_id);

        $contriblist = collect();

        while (true) {

            $this->query_string['before'] = $before;

            $response = $this->retrieve([
                'corporation_id' => $this->getCorporationId(),
                'project_id' => $this->project_id,
            ]);

            $contribs = $response->getBody();
            if (isset($contribs->cursor) && isset($contribs->cursor->before)) {
                $before = $contribs->cursor->before;
            } else {
                break; // empty cursor
            }
            if (isset($contribs->contributors)) {
                $contriblist = $contriblist->concat($contribs->contributors);
            } else {
                // We have reached the end of the dataset
                break;
            }
        }

        // Clear out list if necessary. Disabled under assumption contributors cant leave list
        // CorporationProjectContributor::where('project_id', $this->project_id)->delete();

        $rows = $contriblist->map(function ($item) use ($proj) {
            // handle object or array item
            $id = is_object($item) ? ($item->id ?? null) : ($item['id'] ?? null);
            $contributed = is_object($item) ? ($item->contributed ?? 0) : ($item['contributed'] ?? 0);

            return [
                'project_id' => $proj->id,
                'character_id' => $id,
                'contributed' => $contributed,
            ];
            })->toArray();

        // Perform upsert: unique by project_id + character_id, update contributed on conflict
        // Timestamps are auto updated
        CorporationProjectContributor::upsert(
            $rows,
            uniqueBy: ['project_id', 'character_id'],
            update: ['contributed']
            );

    }
}
