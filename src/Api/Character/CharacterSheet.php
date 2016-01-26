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
use Seat\Eveapi\Models\Character\CharacterSheetCorporationTitles;
use Seat\Eveapi\Models\Character\CharacterSheetImplants;
use Seat\Eveapi\Models\Character\CharacterSheetJumpClone;
use Seat\Eveapi\Models\Character\CharacterSheetJumpCloneImplants;
use Seat\Eveapi\Models\Character\CharacterSheetSkills;

/**
 * Class CharacterSheet
 * @package Seat\Eveapi\Api\Character
 */
class CharacterSheet extends Base
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

            $result = $pheal->CharacterSheet([
                'characterID' => $character->characterID]);

            // The full character sheet is pretty large. We have
            // a few things we can just update the database with
            // but also a whole bunch of things like clones
            // and skills that will need a loop to update.
            // Lets start with the easy stuff first ok.

            // Get the CharacterSheet Data
            $character_data = CharacterSheetModel::firstOrNew([
                'characterID' => $character->characterID]);

            // .. and update it
            $character_data->fill([
                'name'              => $result->name,
                'homeStationID'     => $result->homeStationID,
                'DoB'               => $result->DoB,
                'race'              => $result->race,
                'bloodLineID'       => $result->bloodLineID,
                'bloodLine'         => $result->bloodLine,
                'ancestryID'        => $result->ancestryID,
                'ancestry'          => $result->ancestry,
                'gender'            => $result->gender,
                'corporationName'   => $result->corporationName,
                'corporationID'     => $result->corporationID,
                'allianceName'      => $result->allianceName,
                'allianceID'        => $result->allianceID,
                'factionName'       => $result->factionName,
                'factionID'         => isset($result->factionID) ?
                    $result->factionID : 0,
                'cloneTypeID'       => $result->cloneTypeID,
                'cloneName'         => $result->cloneName,
                'cloneSkillPoints'  => $result->cloneSkillPoints,
                'freeSkillPoints'   => $result->freeSkillPoints,
                'freeRespecs'       => $result->freeRespecs,
                'cloneJumpDate'     => $result->cloneJumpDate,
                'lastRespecDate'    => $result->lastRespecDate,
                'lastTimedRespec'   => $result->lastTimedRespec,
                'remoteStationDate' => $result->remoteStationDate,
                'jumpActivation'    => $result->jumpActivation,
                'jumpFatigue'       => $result->jumpFatigue,
                'jumpLastUpdate'    => $result->jumpLastUpdate,
                'balance'           => $result->balance,
                'intelligence'      => $result->attributes->intelligence,
                'memory'            => $result->attributes->memory,
                'charisma'          => $result->attributes->charisma,
                'perception'        => $result->attributes->perception,
                'willpower'         => $result->attributes->willpower
            ]);

            $character_data->save();

            // Next up, Implants. We need to clear up the ones
            // that we already know of as implants can change
            // at any given time.
            CharacterSheetImplants::where(
                'characterID', $character->characterID)->delete();

            // Lets loop over the implants and create them
            foreach ($result->implants as $implant) {

                CharacterSheetImplants::create([
                    'characterID' => $character->characterID,
                    'typeID'      => $implant->typeID,
                    'typeName'    => $implant->typeName
                ]);

            } // Foreach Implants

            // Next up, Jump Clones. Because we know that Clones can
            // change at any moment, we will have to take the ones
            // we know of and remove them, and then re-add what
            // we got from the API
            CharacterSheetJumpClone::where(
                'characterID', $character->characterID)->delete();
            CharacterSheetJumpCloneImplants::where(
                'characterID', $character->characterID)->delete();

            // Lets loop over the clones for the character.
            foreach ($result->jumpClones as $jump_clone) {

                CharacterSheetJumpClone::create([
                    'characterID' => $character->characterID,
                    'jumpCloneID' => $jump_clone->jumpCloneID,
                    'typeID'      => $jump_clone->typeID,
                    'locationID'  => $jump_clone->locationID,
                    'cloneName'   => $jump_clone->cloneName
                ]);

            } // Foreach JumpClone

            // Lets loop over the Jump Clone Implants for the character
            foreach ($result->jumpCloneImplants as $jump_clone_implant) {

                CharacterSheetJumpCloneImplants::create([
                    'characterID' => $character->characterID,
                    'jumpCloneID' => $jump_clone_implant->jumpCloneID,
                    'typeID'      => $jump_clone_implant->typeID,
                    'typeName'    => $jump_clone_implant->typeName
                ]);
            } // Foreach JumpCloneImplant

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

            // Next, Corporation Titles. Again, this is something that
            // can change as they are granted / revoked, so delete
            // the known once and repopulate
            CharacterSheetCorporationTitles::where(
                'characterID', $character->characterID)->delete();

            // Lets loop over the corporation titles and populate
            foreach ($result->corporationTitles as $title) {

                CharacterSheetCorporationTitles::create([
                    'characterID' => $character->characterID,
                    'titleID'     => $title->titleID,
                    'titleName'   => $title->titleName
                ]);
            } // Foreach Title

        } // Foreach Character

        return;
    }
}
