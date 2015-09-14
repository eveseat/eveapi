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
use Seat\Eveapi\Models\CharacterSkillInTraining;
use Seat\Eveapi\Models\EveApiKey;

/**
 * Class SkillInTraining
 * @package Seat\Eveapi\Api\Character
 */
class SkillInTraining extends Base
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
                ->SkillInTraining([
                    'characterID' => $character->characterID]);

            $training = CharacterSkillInTraining::firstOrNew([
                'characterID' => $character->characterID]);

            $training->fill([
                'currentTQTime'         => $result->currentTQTime->_value,
                'trainingEndTime'       => $result->trainingEndTime,
                'trainingStartTime'     => $result->trainingStartTime,
                'trainingTypeID'        => $result->trainingTypeID,
                'trainingStartSP'       => $result->trainingStartSP,
                'trainingDestinationSP' => $result->trainingDestinationSP,
                'trainingToLevel'       => $result->trainingToLevel,
                'skillInTraining'       => $result->skillInTraining
            ]);

            $training->save();

        }

        return;
    }
}
