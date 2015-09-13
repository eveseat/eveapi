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
use Seat\Eveapi\Models\CharacterPlanetaryRoute;
use Seat\Eveapi\Models\EveApiKey;

/**
 * Class PlanetaryRoutes
 * @package Seat\Eveapi\Api\Character
 */
class PlanetaryRoutes extends Base
{

    /**
     * Run the Update
     *
     * @param \Seat\Eveapi\Models\EveApiKey $api_info
     */
    public function call(EveApiKey $api_info)
    {

        // Routes need to be processed for every planet on
        // every character for the provided API key. We
        // will loop over the planets for the character
        // updating the information as well as clean up
        // the routes that are no longer applicable.

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
                    ->PlanetaryRoutes([
                        'characterID' => $character->characterID,
                        'planetID'    => $planet_id]);

                // Update the Routes
                foreach ($result->routes as $route) {

                    // Get or create the record...
                    $route_info = CharacterPlanetaryRoute::firstOrNew([
                        'ownerID'  => $character->characterID,
                        'planetID' => $planet_id,
                        'routeID'  => $route->routeID]);

                    // ... and set its fields
                    $route_info->fill([
                        'sourcePinID'      => $route->sourcePinID,
                        'destinationPinID' => $route->destinationPinID,
                        'contentTypeID'    => $route->contentTypeID,
                        'contentTypeName'  => $route->contentTypeName,
                        'quantity'         => $route->quantity,
                        'waypoint1'        => $route->waypoint1,
                        'waypoint2'        => $route->waypoint2,
                        'waypoint3'        => $route->waypoint3,
                        'waypoint4'        => $route->waypoint4,
                        'waypoint5'        => $route->waypoint5
                    ]);

                    $route_info->save();

                } // Foreach Routes

                // Cleanup the Routes that are not in the reponse
                // for this specific planet.
                CharacterPlanetaryRoute::where('ownerID', $character->characterID)
                    ->where('planetID', $planet_id)
                    ->whereNotIn('routeID', array_map(function ($route) {

                        return $route->routeID;

                    }, (array)$result->routes))
                    ->delete();

            } // Foreach Planet

            // Cleanup routes for planets that do not exist
            // for this character anymore. It could be that
            // the entire colony was deleted.
            CharacterPlanetaryRoute::where('ownerID', $character->characterID)
                ->whereNotIn('planetID', $colonies)
                ->delete();

        } // Foreach Character

        return;
    }
}
