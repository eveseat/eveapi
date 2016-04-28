<?php
/*
This file is part of SeAT

Copyright (C) 2015, 2016  Leon Jacobs

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

use Seat\Eveapi\Api\Base;
use Seat\Eveapi\Models\Character\CharacterSheet as CharacterSheetModel;
use Seat\Eveapi\Models\Character\CharacterSheetSkills;

/**
 * Class Skills
 * @package Seat\Eveapi\Api\Character
 */
class Skills extends Base
{

    /**
     * Run the Update
     *
     * @return mixed|void
     */
    public function call()
    {

        // TODO: Check if we have access to char/characterSheet
        // and ship this updater as we already have everything
        $pheal = $this->setScope('char')->getPheal();

        foreach ($this->api_info->characters as $character) {

            $result = $pheal->Skills([
                'characterID' => $character->characterID]);

            // Get the CharacterSheet Data
            $character_data = CharacterSheetModel::firstOrNew([
                'characterID' => $character->characterID]);

            // .. and update it with the freeSkillPoints
            $character_data->fill([
                'freeSkillPoints' => $result->freeSkillPoints,
            ]);

            $character_data->save();

            // Next up, Skills. Skills themselves never go away, but
            // obviously update as time goes by. So, lets update or
            // create them as needed
            foreach ($result->skills as $skill) {

                $skill_info = CharacterSheetSkills::firstOrNew([
                    'characterID' => $character->characterID,
                    'typeID'      => $skill->typeID]);

                $skill_info->fill([
                    'skillpoints' => $skill->skillpoints,
                    'level'       => $skill->level,
                    'published'   => $skill->published
                ]);

                $skill_info->save();

            } // Foreach Skills

        } // Foreach Character

        return;
    }
}
