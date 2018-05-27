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

namespace Seat\Eveapi\Jobs\Skills\Character;

use Seat\Eveapi\Jobs\EsiBase;
use Seat\Eveapi\Models\Skills\CharacterSkillQueue;

/**
 * Class Queue.
 * @package Seat\Eveapi\Jobs\Skills\Character
 */
class Queue extends EsiBase
{
    /**
     * @var string
     */
    protected $method = 'get';

    /**
     * @var string
     */
    protected $endpoint = '/characters/{character_id}/skillqueue/';

    /**
     * @var int
     */
    protected $version = 'v2';

    /**
     * @var string
     */
    protected $scope = 'esi-skills.read_skillqueue.v1';

    /**
     * @var array
     */
    protected $tags = ['character', 'skills', 'queue'];

    /**
     * Execute the job.
     *
     * @throws \Throwable
     */
    public function handle()
    {

        if (! $this->preflighted()) return;

        $skill_queue = $this->retrieve([
            'character_id' => $this->getCharacterId(),
        ]);

        if ($skill_queue->isCachedLoad()) return;

        collect($skill_queue)->each(function ($skill) {

            CharacterSkillQueue::firstOrNew([
                'character_id' => $this->getCharacterId(),
                'skill_id'     => $skill->skill_id,
            ])->fill([
                'finish_date'       => property_exists($skill, 'finish_date') ?
                    carbon($skill->finish_date) : null,
                'start_date'        => property_exists($skill, 'start_date') ?
                    carbon($skill->start_date) : null,
                'finished_level'    => $skill->finished_level,
                'queue_position'    => $skill->queue_position,
                'training_start_sp' => property_exists($skill, 'training_start_sp') ?
                    $skill->training_start_sp : null,
                'level_end_sp'      => property_exists($skill, 'level_end_sp') ?
                    $skill->level_end_sp : null,
                'level_start_sp'    => property_exists($skill, 'level_start_sp') ?
                    $skill->level_start_sp : null,
            ])->save();
        });
    }
}
