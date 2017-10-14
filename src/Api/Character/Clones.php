<?php

/*
 * This file is part of SeAT
 *
 * Copyright (C) 2015, 2016, 2017  Leon Jacobs
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

namespace Seat\Eveapi\Api\Character;

use Seat\Eveapi\Api\Base;
use Seat\Eveapi\Models\Character\CharacterSheet as CharacterSheetModel;
use Seat\Eveapi\Models\Character\CharacterSheetImplants;
use Seat\Eveapi\Models\Character\CharacterSheetJumpClone;
use Seat\Eveapi\Models\Character\CharacterSheetJumpCloneImplants;

/**
 * Class Clones.
 * @package Seat\Eveapi\Api\Character
 */
class Clones extends Base
{
    /**
     * Run the Update.
     *
     * @return mixed|void
     */
    public function call()
    {

        // TODO: Check if we have access to char/characterSheet
        // and ship this updater as we already have everything
        $pheal = $this->setScope('char')->getPheal();

        foreach ($this->api_info->characters as $character) {

            $this->writeJobLog('clones',
                'Processing characterID: ' . $character->characterID);

            $result = $pheal->Clones([
                'characterID' => $character->characterID, ]);

            // Get the CharacterSheet Data
            $character_data = CharacterSheetModel::firstOrNew([
                'characterID' => $character->characterID, ]);

            // .. and update it
            $character_data->fill([
                'DoB'               => $result->DoB,
                'race'              => $result->race,
                'bloodLineID'       => $result->bloodLineID,
                'bloodLine'         => $result->bloodLine,
                'ancestryID'        => $result->ancestryID,
                'ancestry'          => $result->ancestry,
                'gender'            => $result->gender,
                'freeRespecs'       => $result->freeRespecs,
                'cloneJumpDate'     => $result->cloneJumpDate,
                'lastRespecDate'    => $result->lastRespecDate,
                'lastTimedRespec'   => $result->lastTimedRespec,
                'remoteStationDate' => $result->remoteStationDate,
                'jumpActivation'    => $result->jumpActivation,
                'jumpFatigue'       => $result->jumpFatigue,
                'jumpLastUpdate'    => $result->jumpLastUpdate,
                'intelligence'      => $result->attributes->intelligence,
                'memory'            => $result->attributes->memory,
                'charisma'          => $result->attributes->charisma,
                'perception'        => $result->attributes->perception,
                'willpower'         => $result->attributes->willpower,
            ]);

            $character_data->save();

            // Next up, Implants. We need to clear up the ones
            // that we already know of as implants can change
            // at any given time.
            CharacterSheetImplants::where(
                'characterID', $character->characterID)->delete();

            // Lets loop over the implants and create them
            foreach ($result->implants as $implant) {

                // avoid entry duplication if more than a worker is working on the same character
                CharacterSheetJumpClone::updateOrCreate([
                        'jumpCloneID' => $jump_clone->jumpCloneID,
                    ],
                    [
                        'characterID' => $character->characterID,
                        'typeID' => $jump_clone->typeID,
                        'locationID' => $jump_clone->locationID,
                        'cloneName' => $jump_clone->cloneName,
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
                    'cloneName'   => $jump_clone->cloneName,
                ]);

            } // Foreach JumpClone

            // Lets loop over the Jump Clone Implants for the character
            foreach ($result->jumpCloneImplants as $jump_clone_implant) {

                CharacterSheetJumpCloneImplants::create([
                    'characterID' => $character->characterID,
                    'jumpCloneID' => $jump_clone_implant->jumpCloneID,
                    'typeID'      => $jump_clone_implant->typeID,
                    'typeName'    => $jump_clone_implant->typeName,
                ]);
            } // Foreach JumpCloneImplant

        } // Foreach Character

    }
}
