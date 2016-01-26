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

return [

    'api'         => [
        Seat\Eveapi\Api\Api\CallList::class
    ],

    'character'   => [
        // The very first call to determine the
        // access mask and characters
        Seat\Eveapi\Api\Account\AccountStatus::class,
        Seat\Eveapi\Api\Eve\CharacterInfo::class,
        Seat\Eveapi\Api\Character\AccountBalance::class,
        Seat\Eveapi\Api\Character\AssetList::class,
        Seat\Eveapi\Api\Character\Bookmarks::class,
        Seat\Eveapi\Api\Character\CharacterSheet::class,
        Seat\Eveapi\Api\Character\ChatChannels::class,
        Seat\Eveapi\Api\Character\ContactList::class,
        Seat\Eveapi\Api\Character\ContactNotifications::class,

        // Contracts are updated first and then the
        // respective items
        Seat\Eveapi\Api\Character\Contracts::class,
        Seat\Eveapi\Api\Character\ContractsItems::class,
        Seat\Eveapi\Api\Character\IndustryJobs::class,
        Seat\Eveapi\Api\Character\KillMails::class,

        // Mail Messages is called first so that the
        // headers are populated for the body updates.
        // This is also a requirement from CCP's side
        // before the body is callable via the API.
        Seat\Eveapi\Api\Character\MailMessages::class,
        Seat\Eveapi\Api\Character\MailBodies::class,
        Seat\Eveapi\Api\Character\MailingLists::class,
        Seat\Eveapi\Api\Character\MarketOrders::class,

        // Notifications is called first so that the
        // texts can be updated.
        Seat\Eveapi\Api\Character\Notifications::class,
        Seat\Eveapi\Api\Character\NotificationTexts::class,

        // Planetary Interaction relies totally on the
        // Colonies to be up to date
        Seat\Eveapi\Api\Character\PlanetaryColonies::class,
        Seat\Eveapi\Api\Character\PlanetaryPins::class,
        Seat\Eveapi\Api\Character\PlanetaryRoutes::class,
        Seat\Eveapi\Api\Character\PlanetaryLinks::class,
        Seat\Eveapi\Api\Character\Research::class,
        Seat\Eveapi\Api\Character\SkillInTraining::class,
        Seat\Eveapi\Api\Character\SkillQueue::class,
        Seat\Eveapi\Api\Character\Standings::class,
        Seat\Eveapi\Api\Character\UpcomingCalendarEvents::class,
        Seat\Eveapi\Api\Character\WalletJournal::class,
        Seat\Eveapi\Api\Character\WalletTransactions::class
    ],

    'corporation' => [
        Seat\Eveapi\Api\Corporation\AccountBalance::class,
        Seat\Eveapi\Api\Corporation\AssetList::class,
        Seat\Eveapi\Api\Corporation\Locations::class,
        Seat\Eveapi\Api\Corporation\Bookmarks::class,
        Seat\Eveapi\Api\Corporation\ContactList::class,
        Seat\Eveapi\Api\Corporation\Contracts::class,
        Seat\Eveapi\Api\Corporation\ContractsItems::class,
        Seat\Eveapi\Api\Corporation\CorporationSheet::class,
        Seat\Eveapi\Api\Corporation\CustomsOffices::class,
        Seat\Eveapi\Api\Corporation\CustomsOfficeLocations::class,
        Seat\Eveapi\Api\Corporation\IndustryJobs::class,
        Seat\Eveapi\Api\Corporation\KillMails::class,
        Seat\Eveapi\Api\Corporation\MarketOrders::class,
        Seat\Eveapi\Api\Corporation\Medals::class,
        Seat\Eveapi\Api\Corporation\MemberMedals::class,
        Seat\Eveapi\Api\Corporation\MemberSecurity::class,
        Seat\Eveapi\Api\Corporation\MemberTracking::class,
        Seat\Eveapi\Api\Corporation\MemberSecurityLog::class,
        Seat\Eveapi\Api\Corporation\Shareholders::class,
        Seat\Eveapi\Api\Corporation\Standings::class,

        // The Listing call should happen before the details
        // call as it depends on itemID's from the list.
        Seat\Eveapi\Api\Corporation\StarbaseList::class,
        Seat\Eveapi\Api\Corporation\StarbaseDetail::class,
        Seat\Eveapi\Api\Corporation\Titles::class,
        Seat\Eveapi\Api\Corporation\WalletJournal::class,
        Seat\Eveapi\Api\Corporation\WalletTransactions::class,
    ],

    'eve'         => [
        Seat\Eveapi\Api\Eve\AllianceList::class,
        Seat\Eveapi\Api\Eve\ConquerableStationList::class,
        Seat\Eveapi\Api\Eve\ErrorList::class,
        Seat\Eveapi\Api\Eve\RefTypes::class
    ],

    'map'         => [
        Seat\Eveapi\Api\Map\Jumps::class,
        Seat\Eveapi\Api\Map\Kills::class,
        Seat\Eveapi\Api\Map\Sovereignty::class
    ],

    'server'      => [
        Seat\Eveapi\Api\Server\ServerStatus::class
    ]
];
