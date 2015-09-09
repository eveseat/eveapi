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
use Seat\Eveapi\Models\CharacterIndustryJob;
use Seat\Eveapi\Models\EveApiKey;

/**
 * Class IndustryJobs
 * @package Seat\Eveapi\Api\Character
 */
class IndustryJobs extends Base
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
                ->IndustryJobs([
                    'characterID' => $character->characterID]);

            // Update the Industry Jobs for the character
            foreach ($result->jobs as $industry_job) {

                $new_industry_job = CharacterIndustryJob::firstOrNew([
                    'characterID' => $character->characterID,
                    'jobID'       => $industry_job->jobID,
                ]);

                $new_industry_job->fill([
                    'installerID'          => $industry_job->installerID,
                    'installerName'        => $industry_job->installerName,
                    'facilityID'           => $industry_job->facilityID,
                    'solarSystemID'        => $industry_job->solarSystemID,
                    'solarSystemName'      => $industry_job->solarSystemName,
                    'stationID'            => $industry_job->stationID,
                    'activityID'           => $industry_job->activityID,
                    'blueprintID'          => $industry_job->blueprintID,
                    'blueprintTypeID'      => $industry_job->blueprintTypeID,
                    'blueprintTypeName'    => $industry_job->blueprintTypeName,
                    'blueprintLocationID'  => $industry_job->blueprintLocationID,
                    'outputLocationID'     => $industry_job->outputLocationID,
                    'runs'                 => $industry_job->runs,
                    'cost'                 => $industry_job->cost,
                    'teamID'               => $industry_job->teamID,
                    'licensedRuns'         => $industry_job->licensedRuns,
                    'probability'          => $industry_job->probability,
                    'productTypeID'        => $industry_job->productTypeID,
                    'productTypeName'      => $industry_job->productTypeName,
                    'status'               => $industry_job->status,
                    'timeInSeconds'        => $industry_job->timeInSeconds,
                    'startDate'            => $industry_job->startDate,
                    'endDate'              => $industry_job->endDate,
                    'pauseDate'            => $industry_job->pauseDate,
                    'completedDate'        => $industry_job->completedDate,
                    'completedCharacterID' => $industry_job->completedCharacterID,
                    'successfulRuns'       => $industry_job->successfulRuns
                ]);

                $new_industry_job->save();

            }
        }

        return;
    }
}
