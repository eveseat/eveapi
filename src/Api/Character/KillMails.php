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
use Seat\Eveapi\Models\Character\KillMail;
use Seat\Eveapi\Models\KillMail\Attacker;
use Seat\Eveapi\Models\KillMail\Detail;
use Seat\Eveapi\Models\KillMail\Item;

/**
 * Class KillMails
 * @package Seat\Eveapi\Api\Character
 */
class KillMails extends Base
{

    /**
     * The amount of row to expect in a
     * API call to the EVE API
     *
     * @var int
     */
    protected $rows_per_call = 1000;

    /**
     * Run the Update
     *
     * @return mixed|void
     */
    public function call()
    {

        // Killmails is another tricky one to update correctly.
        // A few things need to be kept in mind. Refer to the
        // documentation here[1] as the basis for why things are
        // being the done the way it is in this function.
        //
        // First off, we have to keep in mind that if we have more
        // than one API key with the same kill on it, one of them
        // may be the victm. Kill mails have a lot of information
        // in the form of attackers, items and the victim. Most
        // of this information is static regardless if you are the
        // victim or not. In both cases, the killID remains the
        // same, and therefore all of the related information too.
        // So, we will start by using a link table that will allow
        // us to associate the characterID <-> killID relationship.
        // This will help with future queries so that one does not
        // have to use arb things to figure out ownership of the
        // killmails by joining attackers/victims etc. With the
        // link table done, the rest of the killmail information
        // is pretty static and all relates to one unique killID.
        //
        // The next thing to keep in mind is the fact that it is
        // possible to journal walk killmails. We will support that
        // in an effort to get as much historic information as we
        // possibly can. At a very basic level, the implementation
        // will be as follows.
        //  - Start by setting the largest possible PHP number.
        //  - Each request to the EVE API sends this number along.
        //  - While looping over the killmails in the response, we
        //    will check that we keep the from_id as low as the
        //    lowest killID. This will result in the next call to
        //    the API only returning killmails form that killID
        //    backwards.
        //
        // We will also have to do a lookup to check what is the
        // smallest killID that we already know of. If we query
        // the API and reach this number then it will be safe to
        // assume that we have all of the possible killmails for
        // the affected character.
        //
        // [1] https://neweden-dev.com/Char/KillMails

        $pheal = $this->setScope('char')->getPheal();

        foreach ($this->api_info->characters as $character) {

            // Define the first MAX from_id to use when
            // retreiving killmails.
            $from_id = PHP_INT_MAX;

            // This infinite loop needs to be broken out of
            // once we have reached the end of the backwards
            // journal walking. Walking ends when we have
            // either received less rows than asked for, or
            // we have reached a known killID.
            while (true) {

                $result = $pheal->KillMails([
                        'characterID' => $character->characterID,
                        'rowCount'    => $this->rows_per_call

                        // If the from_id is not PHP_INT_MAX, the we can
                        // specify it for the API call. If not, we will
                        // get an error such as:
                        //  Error: 121: Invalid beforeKillID provided.
                    ] + ($from_id == PHP_INT_MAX ? [] : ['fromID' => $from_id])
                );

                foreach ($result->kills as $kill) {

                    // Ensure that $from_id is at its lowest
                    $from_id = min($kill->killID, $from_id);

                    // Check if the killmail is known. If it is,
                    // then we can just continue to the next. We
                    // are assuming the kill details already is
                    // known here.
                    if (KillMail::where('characterID', $character->characterID)
                        ->where('killID', $kill->killID)
                        ->first()
                    ) {
                        continue;
                    }

                    // Create the killmail link to this character
                    KillMail::create([
                        'characterID' => $character->characterID,
                        'killID'      => $kill->killID
                    ]);

                    // With the link complete, we should check if we
                    // have the information for this kill recorded.
                    // If it is already in the database, then there
                    // is simply no need for us to redo all of that
                    // work again. Remember, from this point on, we
                    // refer to a kill by killID, regardless of the
                    // assosiated characterID
                    if (Detail::where('killID', $kill->killID)
                        ->first()
                    ) {
                        continue;
                    }

                    // Create the killDetails, attacker and item info
                    Detail::create([
                        'killID'          => $kill->killID,
                        'solarSystemID'   => $kill->solarSystemID,
                        'killTime'        => $kill->killTime,
                        'moonID'          => $kill->moonID,
                        'characterID'     => $kill->victim->characterID,
                        'characterName'   => $kill->victim->characterName,
                        'corporationID'   => $kill->victim->corporationID,
                        'corporationName' => $kill->victim->corporationName,
                        'allianceID'      => $kill->victim->allianceID,
                        'allianceName'    => $kill->victim->allianceName,
                        'factionID'       => $kill->victim->factionID,
                        'factionName'     => $kill->victim->factionName,
                        'damageTaken'     => $kill->victim->damageTaken,
                        'shipTypeID'      => $kill->victim->shipTypeID
                    ]);

                    foreach ($kill->attackers as $attacker) {

                        Attacker::create([
                            'killID'          => $kill->killID,
                            'characterID'     => $attacker->characterID,
                            'characterName'   => $attacker->characterName,
                            'corporationID'   => $attacker->corporationID,
                            'corporationName' => $attacker->corporationName,
                            'allianceID'      => $attacker->allianceID,
                            'allianceName'    => $attacker->allianceName,
                            'factionID'       => $attacker->factionID,
                            'factionName'     => $attacker->factionName,
                            'securityStatus'  => $attacker->securityStatus,
                            'damageDone'      => $attacker->damageDone,
                            'finalBlow'       => $attacker->finalBlow,
                            'weaponTypeID'    => $attacker->weaponTypeID,
                            'shipTypeID'      => $attacker->shipTypeID
                        ]);
                    }

                    foreach ($kill->items as $item) {

                        Item::create([
                            'killID'       => $kill->killID,
                            'typeID'       => $item->typeID,
                            'flag'         => $item->flag,
                            'qtyDropped'   => $item->qtyDropped,
                            'qtyDestroyed' => $item->qtyDestroyed,
                            'singleton'    => $item->singleton
                        ]);
                    }

                } // Foreach kills

                // As previously mentioned, there may be a few
                // conditions where we may decide its time to
                // break out of the infinite loop. This is where
                // we will be doing those checks. The most ob-
                // vious one being that we may have received less
                // than the total amount of rows asked for.
                if (count($result->kills) < $this->rows_per_call)
                    break;

            } // while(true)

        }

        return;
    }
}
