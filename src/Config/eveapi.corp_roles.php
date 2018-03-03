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

    // A mapping of SSO scope and in game role pairs.
    // This mapping is used within the job workers to check if
    // the current refresh_token has the required in game role
    // to make the requested call. The check itself lives in:
    // \Seat\Eveapi\Jobs\EsiBase::authenticated

    'esi-assets.read_corporation_assets.v1'           => ['Director'],
    'esi-corporations.read_blueprints.v1'             => ['Director'],
    'esi-corporations.read_container_logs.v1'         => ['Director'],
    'esi-corporations.read_divisions.v1'              => ['Director'],
    'esi-corporations.read_facilities.v1'             => ['Factory_Manager'],
    'esi-corporations.read_medals.v1'                 => ['Director'],
    'esi-corporations.track_members.v1'               => ['Director'],
    'esi-corporations.read_titles.v1'                 => ['Director'],
    'esi-corporations.read_outposts.v1'               => ['Director'],
    'esi-corporations.read_corporation_membership.v1' => ['Director'],
    //'esi-characters.read_corporation_roles.v1'        => ['Personnel_Manager'], -> idiot scope, required in order to call /character/{character_id}/roles
    'esi-corporations.read_starbases.v1'              => ['Director'],
    'esi-corporations.read_structures.v1'             => ['Station_Manager'],
    'esi-industry.read_corporation_mining.v1'         => ['Station_Manager'],
    'esi-killmails.read_corporation_killmails.v1'     => ['Director'],
    'esi-markets.read_corporation_orders.v1'          => ['Accountant', 'Trader'],
    'esi-wallet.read_corporation_wallets.v1'          => ['Director', 'Accountant', 'Junior_Accountant'],
    'esi-planets.read_customs_offices.v1'             => ['Director'],
    'esi-assets.read_assets.v1'                       => ['Director'],
];
