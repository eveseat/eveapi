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

use Illuminate\Support\Facades\DB;
use Seat\Eveapi\Api\Base;
use Seat\Eveapi\Models\CharacterPlanetaryPin;
use Seat\Eveapi\Models\EveApiKey;

/**
 * Class PlanetaryPins
 * @package Seat\Eveapi\Api\Character
 */
class PlanetaryPins extends Base
{

    /**
     * Run the Update
     *
     * @param \Seat\Eveapi\Models\EveApiKey $api_info
     */
    public function call(EveApiKey $api_info)
    {

        // Pins need to be processed for every planet on
        // every character for the provided API key. We
        // will loop over the planets for the character
        // updating the information as well as clean up
        // the pins that are no longer applicable.

        // Get an instance of Pheal to use in the updates.
        $pheal = $this->setKey(
            $api_info->key_id, $api_info->v_code)
            ->getPheal();

        foreach ($api_info->characters as $character) {

            // Query the database for known planets from
            // the planetary colonies table that this
            // character owns.
            $colonies = DB::table('character_planetary_colonies')
                ->where('ownerID', $character->characterID)
                ->lists('planetID');

            foreach ($colonies as $planet_id) {

                $result = $pheal->charScope
                    ->PlanetaryPins([
                        'characterID' => $character->characterID,
                        'planetID'    => $planet_id]);

                // Update the Pins
                foreach ($result->pins as $pin) {

                    // Get or create the record...
                    $pin_info = CharacterPlanetaryPin::firstOrNew([
                        'ownerID'  => $character->characterID,
                        'planetID' => $planet_id,
                        'pinID'    => $pin->pinID]);

                    // ... and set its fields
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
                CharacterPlanetaryPin::where('ownerID', $character->characterID)
                    ->where('planetID', $planet_id)
                    ->whereNotIn('pinID', array_map(function ($pin) {

                        return $pin->pinID;

                    }, (array)$result->pins))
                    ->delete();

            } // Foreach Planet

            // Cleanup pins for planets that do not exist
            // for this character anymore. It could be that
            // the entire colony was deleted.
            CharacterPlanetaryPin::where('ownerID', $character->characterID)
                ->whereNotIn('planetID', $colonies)
                ->delete();

        } // Foreach Character

        return;
    }
}
