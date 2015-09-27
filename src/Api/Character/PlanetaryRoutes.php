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

namespace Seat\Eveapi\Api\Character;

use Illuminate\Support\Facades\DB;
use Seat\Eveapi\Api\Base;
use Seat\Eveapi\Models\Character\PlanetaryRoute;

/**
 * Class PlanetaryRoutes
 * @package Seat\Eveapi\Api\Character
 */
class PlanetaryRoutes extends Base
{

    /**
     * Run the Update
     *
     * @return mixed|void
     */
    public function call()
    {

        $pheal = $this->setScope('char')->getPheal();

        // Routes need to be processed for every planet on
        // every character for the provided API key. We
        // will loop over the planets for the character
        // updating the information as well as clean up
        // the routes that are no longer applicable.

        // Loop the key characters
        foreach ($this->api_info->characters as $character) {

            // Query the database for known planets from
            // the planetary colonies table that this
            // character owns.
            $colonies = DB::table('character_planetary_colonies')
                ->where('ownerID', $character->characterID)
                ->lists('planetID');

            foreach ($colonies as $planet_id) {

                $result = $pheal->PlanetaryRoutes([
                    'characterID' => $character->characterID,
                    'planetID'    => $planet_id]);

                foreach ($result->routes as $route) {

                    $route_info = PlanetaryRoute::firstOrNew([
                        'ownerID'  => $character->characterID,
                        'planetID' => $planet_id,
                        'routeID'  => $route->routeID]);

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
                PlanetaryRoute::where('ownerID', $character->characterID)
                    ->where('planetID', $planet_id)
                    ->whereNotIn('routeID', array_map(function ($route) {

                        return $route->routeID;

                    }, (array)$result->routes))
                    ->delete();

            } // Foreach Planet

            // Cleanup routes for planets that do not exist
            // for this character anymore. It could be that
            // the entire colony was deleted.
            PlanetaryRoute::where('ownerID', $character->characterID)
                ->whereNotIn('planetID', $colonies)
                ->delete();

        } // Foreach Character

        return;
    }
}
