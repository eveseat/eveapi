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
use Seat\Eveapi\Models\Character\MailingList;
use Seat\Eveapi\Models\Character\MailingListInfo;

/**
 * Class MailingLists
 * @package Seat\Eveapi\Api\Character
 */
class MailingLists extends Base
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

            $result = $pheal->MailingLists([
                'characterID' => $character->characterID]);

            // Characters can join/leave mailing lists at
            // any time. For this reason, we need to clean
            // the linking table and repopulate the list
            // memberships for this character.
            MailingList::where(
                'characterID', $character->characterID)->delete();

            // Re-populate the mailing lists and info if needed
            foreach ($result->mailingLists as $list) {

                MailingList::create([
                    'characterID' => $character->characterID,
                    'listID'      => $list->listID
                ]);

                // Add the list information if we are not already
                // aware of it.
                MailingListInfo::firstOrCreate([
                    'listID'      => $list->listID,
                    'displayName' => $list->displayName
                ]);
            }
        }

        return;
    }
}
