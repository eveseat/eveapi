<?php

/*
 * This file is part of SeAT
 *
 * Copyright (C) 2015 to present Leon Jacobs
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
    'esi' => [
        'auth' => [
            'client_id' => env('EVE_CLIENT_ID'),
            'client_secret' => env('EVE_CLIENT_SECRET'),
            'client_callback' => env('EVE_CALLBACK_URL'),
        ],
        'service' => [
            'scheme' => env('EVE_ESI_SCHEME', 'https'),
            'host' => env('EVE_ESI_HOST', 'esi.evetech.net'),
            'port' => env('EVE_ESI_PORT', 443),
            'datasource' => env('EVE_ESI_DATASOURCE', 'tranquility'),
        ],
    ],
    'sso' => [
        'service' => [
            'scheme' => env('EVE_SSO_SCHEME', 'https'),
            'host' => env('EVE_SSO_HOST', 'login.eveonline.com'),
            'port' => env('EVE_SSO_PORT', 443),
        ],
    ],
];
