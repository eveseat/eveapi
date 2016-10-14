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

    'version'          => '1.0.18',

    // PhealNG Specific Configuration
    'pheal'            => [
        'cache_path' => storage_path() . '/app/pheal/',
        'log_file'   => storage_path('logs/pheal.log')
    ],

    // Define the keys used in the cache
    'cache_keys'       => [
        'down'                   => 'eve_api_down',
        'down_until'             => 'eve_api_down_until',
        'api_error_count'        => 'eve_api_error_count',
        'connection_error_count' => 'eve_api_conn_error_count'
    ],

    // These limits define how many times either
    // the eveapi should respond with an error status
    // or we fail to connect before we consider the
    // eveapi to be 'down'.
    // See the Rate Limits section here:
    //  https://eveonline-third-party-documentation.
    //      readthedocs.org/en/latest/xmlapi/intro/
    'limits'           => [
        'eveapi_errors'     => 150,
        'connection_errors' => 15
    ],

    // Worker classes present in this array
    // will be ignored globally. This applies
    // to public and authenticated API calls.
    // If the AccountBalance call were to be
    // uncommented, no character updates will
    // have the AccountBalance call made. See
    // Config/eveapi.workers.php for a reference
    // to the possible workers to add here.
    'disabled_workers' => [
        'api'         => [],
        'character'   => [
            // Seat\Eveapi\Api\Character\AccountBalance::class,
        ],
        'corporation' => [],
        'eve'         => [],
        'map'         => [],
        'server'      => []
    ]
];
