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

namespace Seat\Eveapi\Jobs\Character;


use Seat\Eveapi\Jobs\EsiBase;
use Seat\Eveapi\Models\Character\CharacterAgentResearch;

/**
 * Class AgentsResearch
 * @package Seat\Eveapi\Jobs\Character
 */
class AgentsResearch extends EsiBase
{
    /**
     * @var string
     */
    protected $method = 'get';

    /**
     * @var string
     */
    protected $endpoint = '/characters/{character_id}/agents_research/';

    /**
     * @var int
     */
    protected $version = 'v1';

    /**
     * @var string
     */
    protected $scope = 'esi-characters.read_agents_research.v1';

    /**
     * @var array
     */
    protected $tags = ['character', 'agents_research'];

    /**
     * Execute the job.
     *
     * @return void
     * @throws \Exception
     * @throws \Throwable
     */
    public function handle()
    {

        $agents_research = $this->retrieve([
            'character_id' => $this->getCharacterId(),
        ]);

        collect($agents_research)->each(function ($agent_research) {

            CharacterAgentResearch::firstOrNew([
                'character_id' => $this->getCharacterId(),
                'agent_id'     => $agent_research->agent_id,
            ])->fill([
                'skill_type_id'    => $agent_research->skill_type_id,
                'started_at'       => carbon($agent_research->started_at),
                'points_per_day'   => $agent_research->points_per_day,
                'remainder_points' => $agent_research->remainder_points,
            ])->save();
        });

        CharacterAgentResearch::where('character_id', $this->getCharacterId())
            ->whereNotIn('agent_id', collect($agents_research)
                ->pluck('agent_id')->flatten()->all())
            ->delete();
    }
}
