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

use Illuminate\Support\Facades\DB;
use Seat\Eveapi\Api\Base;
use Seat\Eveapi\Models\Character\PlanetaryPin;

/**
 * Class PlanetaryPins
 * @package Seat\Eveapi\Api\Character
 */
class PlanetaryPins extends Base
{

    /**
     * Run the Update
     *
     * @return mixed|void
     */
    public function call()
    {

        $pheal = $this->setScope('char')->getPheal();

        // Pins need to be processed for every planet on
        // every character for the provided API key. We
        // will loop over the planets for the character
        // updating the information as well as clean up
        // the pins that are no longer applicable.

        foreach ($this->api_info->characters as $character) {

            // Query the database for known planets from
            // the planetary colonies table that this
            // character owns.
            $colonies = DB::table('character_planetary_colonies')
                ->where('ownerID', $character->characterID)
                ->pluck('planetID');

            foreach ($colonies as $planet_id) {

                $result = $pheal->PlanetaryPins([
                    'characterID' => $character->characterID,
                    'planetID'    => $planet_id]);

                foreach ($result->pins as $pin) {

                    $pin_info = PlanetaryPin::firstOrNew([
                        'ownerID'  => $character->characterID,
                        'planetID' => $planet_id,
                        'pinID'    => $pin->pinID]);

                    $pin_info->fill([
                        'typeID'           => $pin->typeID,
                        'typeName'         => $pin->typeName,
                        'schematicID'      => $pin->schematicID,
                        'lastLaunchTime'   => $pin->lastLaunchTime,
                        'cycleTime'        => $pin->cycleTime,
                        'quantityPerCycle' => $pin->quantityPerCycle,
                        'installTime'      => $pin->installTime,
                        'expiryTime'       => $pin->expiryTime,
                        'contentTypeID'    => $pin->contentTypeID,
                        'contentTypeName'  => $pin->contentTypeName,
                        'contentQuantity'  => $pin->contentQuantity,
                        'longitude'        => $pin->longitude,
                        'latitude'         => $pin->latitude
                    ]);

                    $pin_info->save();

                } // Foreach Pins

                // Cleanup the Pins that are not in the reponse
                // for this specific planet.
                PlanetaryPin::where('ownerID', $character->characterID)
                    ->where('planetID', $planet_id)
                    ->whereNotIn('pinID', array_map(function ($pin) {

                        return $pin->pinID;

                    }, (array)$result->pins))
                    ->delete();

            } // Foreach Planet

            // Cleanup pins for planets that do not exist
            // for this character anymore. It could be that
            // the entire colony was deleted.
            PlanetaryPin::where('ownerID', $character->characterID)
                ->whereNotIn('planetID', $colonies)
                ->delete();

        } // Foreach Character

        return;
    }
}
