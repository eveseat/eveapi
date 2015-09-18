<?php
/*
This file is part of SeAT

Copyright (C) 2015  Leon Jacobs

This program is free software; you can redistribute it and/or modify
it under the terms of the GNU General Public License as published by
the Free Software Foundation; either version 2 of the License, or
(at your option) any later version.

This program is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
GNU General Public License for more details.

You should have received a copy of the GNU General Public License along
with this program; if not, write to the Free Software Foundation, Inc.,
51 Franklin Street, Fifth Floor, Boston, MA 02110-1301 USA.
*/

namespace Seat\Eveapi\Api\Character;

use Carbon\Carbon;
use Seat\Eveapi\Api\Base;
use Seat\Eveapi\Models\CharacterSkillQueue;

/**
 * Class SkillQueue
 * @package Seat\Eveapi\Api\Character
 */
class SkillQueue extends Base
{

    /**
     * Run the Update
     *
     * @return mixed|void
     */
    public function call()
    {

        $pheal = $this->setScope('char')->getPheal();

        foreach ($this->api_info->characters as $character) {

            $result = $pheal->SkillQueue([
                'characterID' => $character->characterID]);

            // Skill Queues can change at anytime, so we
            // will clean up the current queue and re-
            // poulate it
            CharacterSkillQueue::where(
                'characterID', $character->characterID)->delete();

            $skill_queue = array_filter(
                array_map(function ($skill) use ($character) {

                    return [

                        'characterID'   => $character->characterID,
                        'queuePosition' => $skill->queuePosition,
                        'typeID'        => $skill->typeID,
                        'level'         => $skill->level,
                        'startSP'       => $skill->startSP,
                        'endSP'         => $skill->endSP,
                        'startTime'     => $skill->startTime,
                        'endTime'       => $skill->endTime,

                        // Timestamps
                        'created_at'    => Carbon::now()->toDateTimeString(),
                        'updated_at'    => Carbon::now()->toDateTimeString()
                    ];

                }, (array)$result->skillqueue));

            // If there were any skills derived form the array_map
            // then we can bulk insert it into the table.
            if (count($skill_queue) > 0)
                CharacterSkillQueue::insert($skill_queue);

        }

        return;
    }
}
