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

namespace Seat\Eveapi\Api\Corporation;

use Seat\Eveapi\Api\Base;
use Seat\Eveapi\Models\CorporationContactList;
use Seat\Eveapi\Models\CorporationContactListAlliance;
use Seat\Eveapi\Models\CorporationContactListAllianceLabel;
use Seat\Eveapi\Models\CorporationContactListLabel;

/**
 * Class ContactList
 * @package Seat\Eveapi\Api\Corporation
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

        $pheal = $this->setScope('corp')
            ->setCorporationID()->getPheal();

        $result = $pheal->ContactList();

        // Contact Lists can change just like many other
        // types of information. So, we have to delete
        // the current list and recreate it with the
        // new data we sourced from the API.
        CorporationContactList::where(
            'corporationID', $this->corporationID)->delete();

        foreach ($result->corporateContactList as $contact) {

            CorporationContactList::create([
                'corporationID' => $this->corporationID,
                'contactID'     => $contact->contactID,
                'contactName'   => $contact->contactName,
                'standing'      => $contact->standing,
                'contactTypeID' => $contact->contactTypeID,
                'labelMask'     => $contact->labelMask
            ]);
        }

        // Corporation Contacts also have Labels.
        CorporationContactListLabel::where(
            'corporationID', $this->corporationID)->delete();

        foreach ($result->corporateContactLabels as $label) {

            CorporationContactListLabel::create([
                'corporationID' => $this->corporationID,
                'labelID'       => $label->labelID,
                'name'          => $label->name
            ]);
        }

        // Next up, Alliance Contacts. Exactly the same applies
        // to these as the above corporate contacts
        CorporationContactListAlliance::where(
            'corporationID', $this->corporationID)->delete();

        foreach ($result->allianceContactList as $contact) {

            CorporationContactListAlliance::create([
                'corporationID' => $this->corporationID,
                'contactID'     => $contact->contactID,
                'contactName'   => $contact->contactName,
                'standing'      => $contact->standing,
                'contactTypeID' => $contact->contactTypeID,
                'labelMask'     => $contact->labelMask
            ]);
        }

        // And now, the labels for the Alliance Contact List
        CorporationContactListAllianceLabel::where(
            'corporationID', $this->corporationID)->delete();

        foreach ($result->allianceContactLabels as $label) {

            CorporationContactListAllianceLabel::create([
                'corporationID' => $this->corporationID,
                'labelID'       => $label->labelID,
                'name'          => $label->name
            ]);
        }

        return;
    }
}
