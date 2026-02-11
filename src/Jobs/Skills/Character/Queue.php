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

namespace Seat\Eveapi\Jobs\Skills\Character;

use Seat\Eveapi\Jobs\AbstractAuthCharacterJob;
use Seat\Eveapi\Mapping\Characters\SkillQueueMapping;
use Seat\Eveapi\Models\Skills\CharacterSkillQueue;

/**
 * Class Queue.
 *
 * @package Seat\Eveapi\Jobs\Skills\Character
 */
class Queue extends AbstractAuthCharacterJob
{
    /**
     * @var string
     */
    protected $method = 'get';

    /**
     * @var string
     */
    protected $endpoint = '/characters/{character_id}/skillqueue';

    /**
     * @var string
     */
    protected string $compatibility_date = '2025-07-20';

    /**
     * @var string
     */
    protected $scope = 'esi-skills.read_skillqueue.v1';

    /**
     * @var array
     */
    protected $tags = ['character', 'skill'];

    /**
     * @var int
     */
    protected $greatest_position;

    /**
     * Execute the job.
     *
     * @throws \Throwable
     */
    public function handle()
    {
        parent::handle();

        $this->greatest_position = -1;

        $response = $this->retrieve([
            'character_id' => $this->getCharacterId(),
        ]);

        $skills = $response->getBody();

        collect($skills)->each(function ($skill) {

            $model = CharacterSkillQueue::firstOrNew([
                'character_id' => $this->getCharacterId(),
                'queue_position' => $skill->queue_position,
            ]);

            SkillQueueMapping::make($model, $skill, [
                'character_id' => function () {
                    return $this->getCharacterId();
                },
            ])->save();

            if ($skill->queue_position > $this->greatest_position)
                $this->greatest_position = $skill->queue_position;
        });

        // dropping outdated skills
        CharacterSkillQueue::where('character_id', $this->getCharacterId())
            ->where('queue_position', '>', $this->greatest_position)
            ->delete();
    }
}
