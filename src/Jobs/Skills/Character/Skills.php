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
use Seat\Eveapi\Mapping\Characters\SkillMapping;
use Seat\Eveapi\Models\Character\CharacterInfo;
use Seat\Eveapi\Models\Character\CharacterInfoSkill;
use Seat\Eveapi\Models\Character\CharacterSkill;

/**
 * Class Skills.
 *
 * @package Seat\Eveapi\Jobs\Character
 */
class Skills extends AbstractAuthCharacterJob
{
    /**
     * @var string
     */
    protected $method = 'get';

    /**
     * @var string
     */
    protected $endpoint = '/characters/{character_id}/skills';

    /**
     * @var string
     */
    protected string $compatibility_date = '2025-07-20';

    /**
     * @var string
     */
    protected $scope = 'esi-skills.read_skills.v1';

    /**
     * @var array
     */
    protected $tags = ['character', 'skill'];

    /**
     * Execute the job.
     *
     * @throws \Throwable
     */
    public function handle()
    {
        parent::handle();
        $response = $this->retrieve([
            'character_id' => $this->getCharacterId(),
        ]);

        $skills = $response->getBody();

        CharacterInfo::firstOrCreate(['character_id' => $this->getCharacterId()]);

        CharacterInfoSkill::firstOrNew([
            'character_id' => $this->getCharacterId(),
        ])->fill([
            'total_sp' => $skills->total_sp,
            'unallocated_sp' => property_exists($skills, 'unallocated_sp') ?
                $skills->unallocated_sp : 0,
        ])->save();

        collect($skills->skills)->each(function ($character_skill) {

            $model = CharacterSkill::firstOrNew([
                'character_id' => $this->getCharacterId(),
                'skill_id' => $character_skill->skill_id,
            ]);

            SkillMapping::make($model, $character_skill, [
                'character_id' => function () {
                    return $this->getCharacterId();
                },
            ])->save();
        });

        // delete skills which have been removed
        CharacterSkill::where('character_id', $this->getCharacterId())
            ->whereNotIn('skill_id', collect($skills->skills)->pluck('skill_id')->flatten()->all())
            ->delete();
    }
}
