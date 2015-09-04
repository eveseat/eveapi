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

namespace Seat\Eveapi\Api\Eve;

use Seat\Eveapi\Models\EveAllianceList;
use Seat\Eveapi\Models\EveAllianceListMemberCorporations;
use Seat\Eveapi\Traits\Boot;
use Seat\Eveapi\Traits\Cleanup;
use Seat\Eveapi\Traits\Core;

/**
 * Class AllianceList
 * @package Seat\Eveapi\Server
 */
class AllianceList
{

    use Boot, Core, Cleanup;

    /**
     * Run the AllianceList Update
     */
    public function call()
    {

        $result = $this->getPheal()
            ->eveScope
            ->AllianceList();

        // TODO: Figure out if there is a better way to
        // handle the member corporations stuff
        EveAllianceListMemberCorporations::truncate();

        foreach ($result->alliances as $alliance) {

            // Get or create the Alliance...
            $alliance_data = EveAllianceList::firstOrNew([
                'allianceID' => $alliance->allianceID]);

            // ... and set its fields
            $alliance_data->fill([
                'allianceID'     => $alliance->allianceID,
                'name'           => $alliance->name,
                'shortName'      => $alliance->shortName,
                'executorCorpID' => $alliance->executorCorpID,
                'memberCount'    => $alliance->memberCount,
                'startDate'      => $alliance->startDate
            ]);

            $alliance_data->save();

            // Populate the member corporations for the current
            // alliance
            foreach ($alliance->memberCorporations as $corporation) {

                $alliance_data->members()->save(new EveAllianceListMemberCorporations([
                    'corporationID' => $corporation->corporationID,
                    'startDate'     => $corporation->startDate
                ]));
            }
        }

        return;
    }

}