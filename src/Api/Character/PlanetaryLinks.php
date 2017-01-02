<?php

/*
 * This file is part of SeAT
 *
 * Copyright (C) 2015, 2016, 2017  Leon Jacobs
 *
 * This program is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 2 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License along
 * with this program; if not, write to the Free Software Foundation, Inc.,
 * 51 Franklin Street, Fifth Floor, Boston, MA 02110-1301 USA.
 */

namespace Seat\Eveapi\Api\Character;

use Illuminate\Support\Facades\DB;
use Seat\Eveapi\Api\Base;
use Seat\Eveapi\Models\Character\PlanetaryLink;

/**
 * Class PlanetaryLinks.
 * @package Seat\Eveapi\Api\Character
 */
class PlanetaryLinks extends Base
{
    /**
     * Run the Update.
     *
     * @return mixed|void
     */
    public function call()
    {

        $pheal = $this->setScope('char')->getPheal();

        // Links need to be processed for every planet on
        // every character for the provided API key. We
        // will loop over the planets for the character
        // updating the information as well as clean up
        // the routes that are no longer applicable.

        foreach ($this->api_info->characters as $character) {

            $this->writeJobLog('planetarylinks',
                'Processing characterID: ' . $character->characterID);

            // Query the database for known planets from
            // the planetary colonies table that this
            // character owns.
            $colonies = DB::table('character_planetary_colonies')
                ->where('ownerID', $character->characterID)
                ->pluck('planetID');

            foreach ($colonies as $planet_id) {

                $result = $pheal->PlanetaryLinks([
                    'characterID' => $character->characterID,
                    'planetID'    => $planet_id, ]);

                // There isnt a concept such as a unique
                // linkID, so for now we will just delete
                // all of the link we have for this planet.
                PlanetaryLink::where('ownerID', $character->characterID)
                    ->where('planetID', $planet_id)
                    ->delete();

                // Create the Links
                foreach ($result->links as $link) {

                    PlanetaryLink::create([
                        'ownerID'          => $character->characterID,
                        'planetID'         => $planet_id,
                        'sourcePinID'      => $link->sourcePinID,
                        'destinationPinID' => $link->destinationPinID,
                        'linkLevel'        => $link->linkLevel,
                    ]);

                } // Foreach Links

            } // Foreach Planet

            // Cleanup links for planets that do not exist
            // for this character anymore. It could be that
            // the entire colony was deleted.
            PlanetaryLink::where('ownerID', $character->characterID)
                ->whereNotIn('planetID', $colonies)
                ->delete();

        } // Foreach Character

    }
}
