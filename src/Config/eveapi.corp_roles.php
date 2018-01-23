<?php

/*
 * This file is part of SeAT
 *
 * Copyright (C) 2015, 2016, 2017, 2018  Leon Jacobs
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

return [
    'Director' => [
        'assets'         => [
            \Seat\Eveapi\Jobs\Assets\Corporation\Assets::class,
            \Seat\Eveapi\Jobs\Assets\Corporation\Locations::class,
            \Seat\Eveapi\Jobs\Assets\Corporation\Names::class,
        ],
        'blueprints'     => [
            \Seat\Eveapi\Jobs\Corporation\Blueprints::class,
        ],
        'containerlogs'  => [
            \Seat\Eveapi\Jobs\Corporation\ContainerLogs::class,
        ],
        'divisions'      => [
            \Seat\Eveapi\Jobs\Corporation\Divisions::class,
        ],
        'medalsissued'   => [
            \Seat\Eveapi\Jobs\Corporation\IssuedMedals::class,
        ],
        'members'        => [
            \Seat\Eveapi\Jobs\Corporation\MembersLimit::class,
            \Seat\Eveapi\Jobs\Corporation\MembersTitles::class,
            \Seat\Eveapi\Jobs\Corporation\MemberTracking::class,
        ],
        'outposts'       => [
            \Seat\Eveapi\Jobs\Corporation\Outposts::class,
            \Seat\Eveapi\Jobs\Corporation\OutpostDetails::class,
        ],
        'roles'          => [
            \Seat\Eveapi\Jobs\Corporation\RoleHistories::class,
        ],
        'starbases'      => [
            \Seat\Eveapi\Jobs\Corporation\Starbases::class,
            \Seat\Eveapi\Jobs\Corporation\StarbaseDetails::class,
        ],
        'titles'         => [
            \Seat\Eveapi\Jobs\Corporation\Titles::class,
        ],
        'killmails'      => [
            \Seat\Eveapi\Jobs\Killmails\Corporation\Recent::class,
        ],
        'customsoffices' => [
            //
        ],
    ],

    'Factory_Manager' => [
        'fascilities' => [
            \Seat\Eveapi\Jobs\Corporation\Facilities::class,
        ],
    ],

    'Station_Manager' => [
        \Seat\Eveapi\Jobs\Corporation\Structures::class,
        \Seat\Eveapi\Jobs\Industry\Corporation\Mining\Extractions::class,
    ],

    'Accountant' => [
        \Seat\Eveapi\Jobs\Industry\Corporation\Mining\Observers::class,
        \Seat\Eveapi\Jobs\Industry\Corporation\Mining\ObserverDetails::class,
    ],

    'Accountant|Trader' => [
        \Seat\Eveapi\Jobs\Market\Corporation\Orders::class,
    ],

    'Accountant|Junior_Accountant' => [
        \Seat\Eveapi\Jobs\Wallet\Corporation\Balances::class,
        \Seat\Eveapi\Jobs\Wallet\Corporation\Journals::class,
        \Seat\Eveapi\Jobs\Wallet\Corporation\Transactions::class,
    ],

    'Factory_Manager' => [
        \Seat\Eveapi\Jobs\Industry\Corporation\Jobs::class,
    ],

    // Jobs that only require that you are a member om a corp.
    'Member'          => [
        'bookmarks'    => [
            \Seat\Eveapi\Jobs\Bookmarks\Corporation\Bookmarks::class,
            \Seat\Eveapi\Jobs\Bookmarks\Corporation\Folders::class,
        ],
        'contacts'     => [
            \Seat\Eveapi\Jobs\Contacts\Corporation\Contacts::class,
        ],
        'contracts'    => [
            \Seat\Eveapi\Jobs\Contracts\Corporation\Contracts::class,
            \Seat\Eveapi\Jobs\Contracts\Corporation\Items::class,
            \Seat\Eveapi\Jobs\Contracts\Corporation\Bids::class,
        ],
        'info'         => [
            \Seat\Eveapi\Jobs\Corporation\Info::class,
            \Seat\Eveapi\Jobs\Corporation\AllianceHistory::class,
        ],
        'medals'       => [
            \Seat\Eveapi\Jobs\Corporation\Medals::class,
        ],
        'members'      => [
            \Seat\Eveapi\Jobs\Corporation\Members::class,
        ],
        'roles'        => [
            \Seat\Eveapi\Jobs\Corporation\Roles::class,
        ],
        'shareholders' => [
            \Seat\Eveapi\Jobs\Corporation\Shareholders::class,
        ],
        'standings'    => [
            \Seat\Eveapi\Jobs\Corporation\Standings::class,
        ],
    ],
];
