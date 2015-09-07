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

use Seat\Eveapi\Api\Base;
use Seat\Eveapi\Models\CharacterContactList;
use Seat\Eveapi\Models\CharacterContactListAlliance;
use Seat\Eveapi\Models\CharacterContactListAllianceLabel;
use Seat\Eveapi\Models\CharacterContactListCorporate;
use Seat\Eveapi\Models\CharacterContactListCorporateLabel;
use Seat\Eveapi\Models\CharacterContactListLabel;
use Seat\Eveapi\Models\EveApiKey;

/**
 * Class ContactList
 * @package Seat\Eveapi\Api\Character
 */
class ContactList extends Base
{

    /**
     * Run the Update
     *
     * @param \Seat\Eveapi\Models\EveApiKey $api_info
     */
    public function call(EveApiKey $api_info)
    {

        // Ofc, we need to process the update of all
        // of the characters on this key.
        foreach ($api_info->characters as $character) {

            $result = $this->setKey(
                $api_info->key_id, $api_info->v_code)
                ->getPheal()
                ->charScope
                ->ContactList([
                    'characterID' => $character->characterID]);

            // Contact Lists can change just like many other
            // types of information. So, we have to delete
            // the current list and recreate it with the
            // new data we sourced from the API.
            CharacterContactList::where(
                'characterID', $character->characterID)->delete();

            foreach ($result->contactList as $contact) {

                CharacterContactList::create([
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
            CharacterContactListLabel::where(
                'characterID', $character->characterID)->delete();

            foreach ($result->contactLabels as $label) {

                CharacterContactListLabel::create([
                    'characterID' => $character->characterID,
                    'labelID'     => $label->labelID,
                    'name'        => $label->name
                ]);
            }

            // Characters also expose Corp / Alliance contacts
            // information. As these can also change we will
            // update them as needed
            CharacterContactListCorporate::where(
                'characterID', $character->characterID)->delete();

            foreach ($result->corporateContactList as $contact) {

                CharacterContactListCorporate::create([
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
            CharacterContactListCorporateLabel::where(
                'characterID', $character->characterID)->delete();

            foreach ($result->corporateContactLabels as $label) {

                CharacterContactListCorporateLabel::create([
                    'characterID'   => $character->characterID,
                    'corporationID' => $character->corporationID,
                    'labelID'       => $label->labelID,
                    'name'          => $label->name
                ]);
            }

            // Next up, Alliance Contacts. Exactly the same applies
            // to these as the above personal / corporate contacts
            CharacterContactListAlliance::where(
                'characterID', $character->characterID)->delete();

            foreach ($result->allianceContactList as $contact) {

                CharacterContactListAlliance::create([
                    'characterID'   => $character->characterID,
                    'contactID'     => $contact->contactID,
                    'contactName'   => $contact->contactName,
                    'standing'      => $contact->standing,
                    'contactTypeID' => $contact->contactTypeID,
                    'labelMask'     => $contact->labelMask
                ]);
            }

            // And now, the labels for the Alliance Contact List
            CharacterContactListAllianceLabel::where(
                'characterID', $character->characterID)->delete();

            foreach ($result->allianceContactLabels as $label) {

                CharacterContactListAllianceLabel::create([
                    'characterID' => $character->characterID,
                    'labelID'     => $label->labelID,
                    'name'        => $label->name
                ]);
            }

        } // Foreach Character

        return;
    }
}
