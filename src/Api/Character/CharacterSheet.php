<?php
/*
The MIT License (MIT)

Copyright (c) 2015 Leon Jacobs
Copyright (c) 2015 eveseat

Permission is hereby granted, free of charge, to any person obtaining a copy
of this software and associated documentation files (the "Software"), to deal
in the Software without restriction, including without limitation the rights
to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
copies of the Software, and to permit persons to whom the Software is
furnished to do so, subject to the following conditions:

The above copyright notice and this permission notice shall be included in all
copies or substantial portions of the Software.

THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN THE
SOFTWARE.
*/

namespace Seat\Eveapi\Api\Character;

use Seat\Eveapi\Api\Base;
use Seat\Eveapi\Models\CharacterAccountBalance;
use Seat\Eveapi\Models\CharacterCharacterSheet;
use Seat\Eveapi\Models\CharacterCharacterSheetCorporationTitles;
use Seat\Eveapi\Models\CharacterCharacterSheetImplants;
use Seat\Eveapi\Models\CharacterCharacterSheetJumpClone;
use Seat\Eveapi\Models\CharacterCharacterSheetJumpCloneImplants;
use Seat\Eveapi\Models\CharacterCharacterSheetSkills;
use Seat\Eveapi\Models\EveApiKey;

/**
 * Class CharacterSheet
 * @package Seat\Eveapi\Api\Character
 */
class CharacterSheet extends Base
{

    /**
     * Run the Update
     *
     * @param \Seat\Eveapi\Models\EveApiKey $api_info
     */
    public function call(EveApiKey $api_info)
    {

        // Ofc, we need to process the update of all
        // of the characters on this key.
        foreach ($api_info->characters as $character) {

            $result = $this->setKey(
                $api_info->key_id, $api_info->v_code)
                ->getPheal()
                ->charScope
                ->CharacterSheet([
                    'characterID' => $character->characterID]);

            // The full character sheet is pretty large. We have
            // a few things we can just update the datbase with
            // but also a whole bunch of things like clones
            // and skills that will need a loop to update.
            // Lets start with the easy stuff first ok.

            // Get the CharacterSheet Data
            $character_data = CharacterCharacterSheet::firstOrNew([
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
                'factionID'         => $result->factionID,
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
            CharacterCharacterSheetImplants::where(
                'characterID', $character->characterID)->delete();

            // Lets loop over the implants and create them
            foreach ($result->implants as $implant) {

                $character_data->implants()->save(new CharacterCharacterSheetImplants([
                    'typeID'   => $implant->typeID,
                    'typeName' => $implant->typeName
                ]));

            } // Foreach Implants

            // Next up, Jump Clones. Because we know that Clones can
            // change at any moment, we will have to take the ones
            // we know of and remove them, and then re-add what
            // we got from the API
            CharacterCharacterSheetJumpClone::where(
                'characterID', $character->characterID)->delete();
            CharacterCharacterSheetJumpCloneImplants::where(
                'characterID', $character->characterID)->delete();

            // Lets loop over the clones for the character.
            foreach ($result->jumpClones as $jump_clone) {

                $character_data->jump_clones()->save(new CharacterCharacterSheetJumpClone([
                    'jumpCloneID' => $jump_clone->jumpCloneID,
                    'typeID'      => $jump_clone->typeID,
                    'locationID'  => $jump_clone->locationID,
                    'cloneName'   => $jump_clone->cloneName
                ]));

            } // Foreach JumpClone

            // Lets loop over the Jump Clone Implants for the character
            foreach ($result->jumpCloneImplants as $jump_clone_implant) {

                CharacterCharacterSheetJumpCloneImplants::create([
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

                $skill_info = CharacterCharacterSheetSkills::firstOrNew([
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
            CharacterCharacterSheetCorporationTitles::where(
                'characterID', $character->characterID)->delete();

            // Lets loop over the corporation titles and populate
            foreach ($result->corporationTitles as $title) {

                $character_data->corporation_titles()->save(
                    new CharacterCharacterSheetCorporationTitles([
                        'titleID'   => $title->titleID,
                        'titleName' => $title->titleName
                    ])
                );
            } // Foreach Title

        } // Foreach Character

        return;
    }
}
