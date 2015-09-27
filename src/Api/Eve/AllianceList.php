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
use Seat\Eveapi\Models\Eve\AllianceList as AllianceListModel;
use Seat\Eveapi\Models\Eve\AllianceListMemberCorporations;

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

        $result = $this->setScope('eve')
            ->getPheal()
            ->AllianceList();

        foreach ($result->alliances as $alliance) {

            $alliance_data = AllianceListModel::firstOrNew([
                'allianceID' => $alliance->allianceID]);

            $alliance_data->fill([
                'name'           => $alliance->name,
                'shortName'      => $alliance->shortName,
                'executorCorpID' => $alliance->executorCorpID,
                'memberCount'    => $alliance->memberCount,
                'startDate'      => $alliance->startDate
            ]);

            $alliance_data->save();

            // Get a list of known corporationID's for this
            // alliance
            $known_corporations = AllianceListMemberCorporations::where(
                'allianceID', $alliance->allianceID)
                ->lists('corporationID')->all();

            // Populate the member corporations for the current
            // alliance if the corporationID is not in the
            // known_corporations list
            foreach ($alliance->memberCorporations as $corporation) {

                if (!in_array($corporation->corporationID, $known_corporations))
                    AllianceListMemberCorporations::create([
                        'allianceID'    => $alliance->allianceID,
                        'corporationID' => $corporation->corporationID,
                        'startDate'     => $corporation->startDate
                    ]);
            }

            // Cleanup Corporations that are no longer part
            // of this alliance
            AllianceListMemberCorporations::where('allianceID', $alliance->allianceID)
                ->whereNotIn('corporationID', array_map(function ($corporation) {

                    return $corporation->corporationID;

                }, (array)$alliance->memberCorporations))
                ->delete();
        }

        return;
    }

}
