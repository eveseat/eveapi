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

use Seat\Eveapi\Api\Base;
use Seat\Eveapi\Models\Character\ContactList as ContactListModel;
use Seat\Eveapi\Models\Character\ContactListAlliance;
use Seat\Eveapi\Models\Character\ContactListAllianceLabel;
use Seat\Eveapi\Models\Character\ContactListCorporate;
use Seat\Eveapi\Models\Character\ContactListCorporateLabel;
use Seat\Eveapi\Models\Character\ContactListLabel;

/**
 * Class ContactList
 * @package Seat\Eveapi\Api\Character
 */
class ContactList extends Base
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

            $result = $pheal->ContactList([
                'characterID' => $character->characterID]);

            // Contact Lists can change just like many other
            // types of information. So, we have to delete
            // the current list and recreate it with the
            // new data we sourced from the API.
            ContactListModel::where(
                'characterID', $character->characterID)->delete();

            foreach ($result->contactList as $contact) {

                ContactListModel::create([
                    'characterID'   => $character->characterID,
                    'contactID'     => $contact->contactID,
                    'contactName'   => $contact->contactName,
                    'standing'      => $contact->standing,
                    'contactTypeID' => $contact->contactTypeID,
                    'labelMask'     => $contact->labelMask,
                    'inWatchlist'   => $contact->inWatchlist
                ]);
            }

            // Next up, the Contact List Labels
            ContactListLabel::where(
                'characterID', $character->characterID)->delete();

            foreach ($result->contactLabels as $label) {

                ContactListLabel::create([
                    'characterID' => $character->characterID,
                    'labelID'     => $label->labelID,
                    'name'        => $label->name
                ]);
            }

            // Characters also expose Corp / Alliance contacts
            // information. As these can also change we will
            // update them as needed
            ContactListCorporate::where(
                'characterID', $character->characterID)->delete();

            foreach ($result->corporateContactList as $contact) {

                ContactListCorporate::create([
                    'characterID'   => $character->characterID,
                    'corporationID' => $character->corporationID,
                    'contactID'     => $contact->contactID,
                    'contactName'   => $contact->contactName,
                    'standing'      => $contact->standing,
                    'contactTypeID' => $contact->contactTypeID,
                    'labelMask'     => $contact->labelMask
                ]);
            }

            // Corporation Contacts also have Labels.
            ContactListCorporateLabel::where(
                'characterID', $character->characterID)->delete();

            foreach ($result->corporateContactLabels as $label) {

                ContactListCorporateLabel::create([
                    'characterID'   => $character->characterID,
                    'corporationID' => $character->corporationID,
                    'labelID'       => $label->labelID,
                    'name'          => $label->name
                ]);
            }

            // Next up, Alliance Contacts. Exactly the same applies
            // to these as the above personal / corporate contacts
            ContactListAlliance::where(
                'characterID', $character->characterID)->delete();

            foreach ($result->allianceContactList as $contact) {

                ContactListAlliance::create([
                    'characterID'   => $character->characterID,
                    'contactID'     => $contact->contactID,
                    'contactName'   => $contact->contactName,
                    'standing'      => $contact->standing,
                    'contactTypeID' => $contact->contactTypeID,
                    'labelMask'     => $contact->labelMask
                ]);
            }

            // And now, the labels for the Alliance Contact List
            ContactListAllianceLabel::where(
                'characterID', $character->characterID)->delete();

            foreach ($result->allianceContactLabels as $label) {

                ContactListAllianceLabel::create([
                    'characterID' => $character->characterID,
                    'labelID'     => $label->labelID,
                    'name'        => $label->name
                ]);
            }

        } // Foreach Character

        return;
    }
}
