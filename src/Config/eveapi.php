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

return [

    'version'     => '1.0',

    // Defines the workers that is available to use.
    'workers'     => [

        'api'         => [
            \Seat\Eveapi\Api\Api\CallList::class
        ],

        'character'   => [
            // The very first call to determine the
            // access mask and characters
            \Seat\Eveapi\Api\Account\AccountStatus::class,
            \Seat\Eveapi\Api\Eve\CharacterInfo::class,
            \Seat\Eveapi\Api\Character\AccountBalance::class,
            \Seat\Eveapi\Api\Character\AssetList::class,
            \Seat\Eveapi\Api\Character\Bookmarks::class,
            \Seat\Eveapi\Api\Character\CharacterSheet::class,
            \Seat\Eveapi\Api\Character\ChatChannels::class,
            \Seat\Eveapi\Api\Character\ContactList::class,
            \Seat\Eveapi\Api\Character\ContactNotifications::class,

            // Contracts are updated first and then the
            // respective items
            \Seat\Eveapi\Api\Character\Contracts::class,
            \Seat\Eveapi\Api\Character\ContractsItems::class,
            \Seat\Eveapi\Api\Character\IndustryJobs::class,
            \Seat\Eveapi\Api\Character\KillMails::class,

            // Mail Messages is called first so that the
            // headers are populated for the body updates.
            // This is also a requirement from CCP's side
            // before the body is callable via the API.
            \Seat\Eveapi\Api\Character\MailMessages::class,
            \Seat\Eveapi\Api\Character\MailBodies::class,
            \Seat\Eveapi\Api\Character\MailingLists::class,
            \Seat\Eveapi\Api\Character\MarketOrders::class,

            // Notifications is called first so that the
            // texts can be updated.
            \Seat\Eveapi\Api\Character\Notifications::class,
            \Seat\Eveapi\Api\Character\NotificationTexts::class,

            // Planetary Interaction relies totally on the
            // Colonies to be up to date
            \Seat\Eveapi\Api\Character\PlanetaryColonies::class,
            \Seat\Eveapi\Api\Character\PlanetaryPins::class,
            \Seat\Eveapi\Api\Character\PlanetaryRoutes::class,
            \Seat\Eveapi\Api\Character\PlanetaryLinks::class,
            \Seat\Eveapi\Api\Character\Research::class,
            \Seat\Eveapi\Api\Character\SkillInTraining::class,
            \Seat\Eveapi\Api\Character\SkillQueue::class,
            \Seat\Eveapi\Api\Character\Standings::class,
            \Seat\Eveapi\Api\Character\UpcomingCalendarEvents::class,
            \Seat\Eveapi\Api\Character\WalletJournal::class,
            \Seat\Eveapi\Api\Character\WalletTransactions::class
        ],

        'corporation' => [
            Seat\Eveapi\Api\Corporation\AccountBalance::class,
            Seat\Eveapi\Api\Corporation\AssetList::class,
            Seat\Eveapi\Api\Corporation\Locations::class,
        ],

        'eve'         => [
            \Seat\Eveapi\Api\Eve\AllianceList::class,
            \Seat\Eveapi\Api\Eve\ConquerableStationList::class,
            \Seat\Eveapi\Api\Eve\ErrorList::class,
            \Seat\Eveapi\Api\Eve\RefTypes::class
        ],

        'map'         => [
            \Seat\Eveapi\Api\Map\Jumps::class,
            \Seat\Eveapi\Api\Map\Kills::class,
            \Seat\Eveapi\Api\Map\Sovereignty::class
        ],

        'server'      => [
            \Seat\Eveapi\Api\Server\ServerStatus::class
        ]
    ],

    // The access mask definitions used by the API updater
    // to ensure that keys have access to certian calls.

    // select concat("'", lcase(name), "'", '=>', accessMask, ",")
    // from `eve_api_call_lists` where type ='Character';
    // + Manual entries.

    // AccountStatus is moved here as its the only one currently
    // that has a mask requirement as well as the fact that
    // Account keys simply mean that all the characters are visible.
    'access_bits' => [

        'char' => [

            'chatchannels'           => 536870912,
            'bookmarks'              => 268435456,
            'locations'              => 134217728,
            'contracts'              => 67108864,
            'contractitems'          => 67108864,
            'accountstatus'          => 33554432,
            'characterinfo'          => 8388608,
            'wallettransactions'     => 4194304,
            'walletjournal'          => 2097152,
            'upcomingcalendarevents' => 1048576,
            'standings'              => 524288,
            'skillqueue'             => 262144,
            'skillintraining'        => 131072,
            'research'               => 65536,
            'notificationtexts'      => 32768,
            'notifications'          => 16384,
            'medals'                 => 8192,
            'marketorders'           => 4096,
            'mailmessages'           => 2048,
            'mailinglists'           => 1024,
            'mailbodies'             => 512,
            'killmails'              => 256,
            'industryjobs'           => 128,
            'facwarstats'            => 64,
            'contactnotifications'   => 32,
            'contactlist'            => 16,
            'charactersheet'         => 8,
            'calendareventattendees' => 4,
            'planetarycolonies'      => 2,
            'planetarypins'          => 2,
            'planetaryroutes'        => 2,
            'planetarylinks'         => 2,
            'assetlist'              => 2,
            'accountbalance'         => 1,
        ],

        // select concat("'", lcase(name), "'", '=>', `accessMask`, ",")
        // from `eve_api_call_lists` where type ='Corporation';
        // + Manual entries.
        'corp' => [

            'bookmarks'              => 67108864,
            'membertrackingextended' => 33554432,
            'locations'              => 16777216,
            'contracts'              => 8388608,
            'titles'                 => 4194304,
            'wallettransactions'     => 2097152,
            'walletjournal'          => 1048576,
            'starbaselist'           => 524288,
            'standings'              => 262144,
            'starbasedetail'         => 131072,
            'shareholders'           => 65536,
            'outpostservicedetail'   => 32768,
            'outpostlist'            => 16384,
            'medals'                 => 8192,
            'marketorders'           => 4096,
            'membertrackinglimited'  => 2048,
            'membersecuritylog'      => 1024,
            'membersecurity'         => 512,
            'killmails'              => 256,
            'industryjobs'           => 128,
            'facwarstats'            => 64,
            'containerlog'           => 32,
            'contactlist'            => 16,
            'corporationsheet'       => 8,
            'membermedals'           => 4,
            'assetlist'              => 2,
            'accountbalance'         => 1,
        ],
    ]
];
