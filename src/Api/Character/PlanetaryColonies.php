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
use Seat\Eveapi\Models\Character\PlanetaryColony;

/**
 * Class PlanetaryColonies
 * @package Seat\Eveapi\Api\Character
 */
class PlanetaryColonies extends Base
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

            $this->writeJobLog('planetarycolonies',
                'Processing characterID: ' . $character->characterID);

            $result = $pheal->PlanetaryColonies([
                'characterID' => $character->characterID]);

            $this->writeJobLog('planetarycolonies',
                'API responsed with ' . count($result->colonies) . ' colonies');

            // Update the Planetary Colonies for the character.
            // As this can obiously change a lot, we will have
            // to clean up the ones that we did not receive in
            // this update call.
            foreach ($result->colonies as $colony) {

                $colony_info = PlanetaryColony::firstOrNew([
                    'ownerID'  => $colony->ownerID,
                    'planetID' => $colony->planetID]);

                $colony_info->fill([
                    'solarSystemID'   => $colony->solarSystemID,
                    'solarSystemName' => $colony->solarSystemName,
                    'planetName'      => $colony->planetName,
                    'planetTypeID'    => $colony->planetTypeID,
                    'planetTypeName'  => $colony->planetTypeName,
                    'ownerID'         => $colony->ownerID,
                    'ownerName'       => $colony->ownerName,
                    'lastUpdate'      => $colony->lastUpdate,
                    'upgradeLevel'    => $colony->upgradeLevel,
                    'numberOfPins'    => $colony->numberOfPins
                ]);

                $colony_info->save();

            } // Foreach colony

            // Cleanup the Colonies that are not in the reponse.
            PlanetaryColony::where('ownerID', $character->characterID)
                ->whereNotIn('planetID', array_map(function ($colony) {

                    return $colony->planetID;

                }, (array)$result->colonies))
                ->delete();
        }

        return;
    }
}
