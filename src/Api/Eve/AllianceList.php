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

namespace Seat\Eveapi\Api\Eve;

use Seat\Eveapi\Api\Base;
use Seat\Eveapi\Models\EveAllianceList;
use Seat\Eveapi\Models\EveAllianceListMemberCorporations;

/**
 * Class AllianceList
 * @package Seat\Eveapi\Server
 */
class AllianceList extends Base
{

    /**
     * Run the Update
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