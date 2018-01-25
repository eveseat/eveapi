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

namespace Seat\Eveapi\Jobs\Corporation;


use Seat\Eveapi\Jobs\EsiBase;
use Seat\Eveapi\Models\Corporation\CorporationRole;

/**
 * Class Roles
 * @package Seat\Eveapi\Jobs\Corporation
 */
class Roles extends EsiBase
{
    /**
     * @var string
     */
    protected $method = 'get';

    /**
     * @var string
     */
    protected $endpoint = '/corporations/{corporation_id}/roles/';

    /**
     * @var string
     */
    protected $version = 'v1';

    /**
     * @var string
     */
    protected $scope = 'esi-characters.read_corporation_roles.v1';

    /**
     * @var array
     */
    protected $tags = ['corporation', 'roles'];

    /**
     * @var array
     */
    protected $types = [
        'roles',
        'grantable_roles',
        'roles_at_hq',
        'grantable_roles_at_hq',
        'roles_at_base',
        'grantable_roles_at_base',
        'roles_at_other',
        'grantable_roles_at_other',
    ];

    /**
     * Execute the job.
     *
     * @throws \Throwable
     */
    public function handle()
    {

        $roles = $this->retrieve([
            'corporation_id' => $this->getCorporationId(),
        ]);

        collect($roles)->each(function ($role) {

            collect($this->types)->each(function ($type) use ($role) {

                if (! property_exists($role, $type))
                    return;

                collect($role->{$type})->each(function ($name) use ($role, $type) {

                    CorporationRole::firstOrCreate([
                        'corporation_id' => $this->getCorporationId(),
                        'character_id'   => $role->character_id,
                        'type'           => $type,
                        'role'           => $name,
                    ]);

                });

                CorporationRole::where('corporation_id', $this->getCorporationId())
                    ->where('character_id', $role->character_id)
                    ->where('type', $type)
                    ->whereNotIn('role', collect($role->{$type})->flatten()->all())
                    ->delete();
            });
        });
    }
}
