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
use Seat\Eveapi\Models\Character\CharacterInfo;
use Seat\Eveapi\Models\Character\CharacterInfoSkill;
use Seat\Eveapi\Models\Character\CharacterSkill;

/**
 * Class Skills.
 * @package Seat\Eveapi\Jobs\Character
 */
class Skills extends EsiBase
{
    /**
     * @var string
     */
    protected $method = 'get';

    /**
     * @var string
     */
    protected $endpoint = '/characters/{character_id}/skills/';

    /**
     * @var string
     */
    protected $version = 'v4';

    /**
     * @var string
     */
    protected $scope = 'esi-skills.read_skills.v1';

    /**
     * @var array
     */
    protected $tags = ['character', 'skills'];

    /**
     * Execute the job.
     *
     * @throws \Throwable
     */
    public function handle()
    {

        if (! $this->preflighted()) return;

        $character_skills = $this->retrieve([
            'character_id' => $this->getCharacterId(),
        ]);

        if ($character_skills->isCachedLoad()) return;

        CharacterInfo::firstOrCreate(['character_id' => $this->getCharacterId()]);

        CharacterInfoSkill::firstOrNew(['character_id' => $this->getCharacterId()])->fill([
            'total_sp'       => $character_skills->total_sp,
            'unallocated_sp' => property_exists($character_skills, 'unallocated_sp') ?
                $character_skills->unallocated_sp : 0,
        ])->save();

        collect($character_skills->skills)->each(function ($character_skill) {

            CharacterSkill::firstOrNew([
                'character_id' => $this->getCharacterId(),
                'skill_id'     => $character_skill->skill_id,
            ])->fill([
                'skillpoints_in_skill' => $character_skill->skillpoints_in_skill,
                'trained_skill_level'  => $character_skill->trained_skill_level,
                'active_skill_level'   => $character_skill->active_skill_level,
            ])->save();
        });
    }
}
