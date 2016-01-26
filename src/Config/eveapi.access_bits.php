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

    // The access mask definitions used by the API updater
    // to ensure that keys have access to certian calls.

    // select concat("'", lcase(name), "'", '=>', accessMask, ",")
    // from `eve_api_call_lists` where type ='Character';
    // + Manual entries.

    // AccountStatus is moved here as its the only one currently
    // that has a mask requirement as well as the fact that
    // Account keys simply mean that all the characters are visible.

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
        'contractitems'          => 8388608,
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
        'membertracking'         => 2048,
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
        'customsoffices'         => 2,
        'accountbalance'         => 1,
    ]
];
