<?php

/*
 * This file is part of SeAT
 *
 * Copyright (C) 2015 to 2020 Leon Jacobs
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

    'version'               => '3.0.20',

    // API Joblog logging
    'enable_joblog'         => false,

    'eseye_logfile'         => storage_path('logs'),
    'eseye_cache'           => storage_path('eseye'),
    'eseye_loglevel'        => 'info', // valid entries are RFC 5424 levels ('debug', 'info', 'warn', 'error')

    'eseye_client_id'       => env('EVE_CLIENT_ID'),
    'eseye_client_secret'   => env('EVE_CLIENT_SECRET'),
    'eseye_client_callback' => env('EVE_CALLBACK_URL'),

    'eseye_esi_scheme'      => env('EVE_ESI_SCHEME', 'https'),
    'eseye_esi_host'        => env('EVE_ESI_HOST', 'esi.evetech.net'),
    'eseye_esi_port'        => env('EVE_ESI_PORT', 443),
    'eseye_esi_datasource'  => env('EVE_ESI_DATASOURCE', 'tranquility'),
    'eseye_sso_scheme'      => env('EVE_SSO_SCHEME', 'https'),
    'eseye_sso_host'        => env('EVE_SSO_HOST', 'login.eveonline.com'),
    'eseye_sso_port'        => env('EVE_SSO_PORT', 443),
];
