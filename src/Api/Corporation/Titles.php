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

namespace Seat\Eveapi\Api\Corporation;

use Seat\Eveapi\Api\Base;
use Seat\Eveapi\Models\Corporation\Title;

/**
 * Class Titles
 * @package Seat\Eveapi\Api\Corporation
 */
class Titles extends Base
{

    /**
     * Run the Update
     *
     * @return mixed|void
     */
    public function call()
    {

        $pheal = $this->setScope('corp')
            ->setCorporationID()->getPheal();

        $result = $pheal->Titles();

        $this->writeJobLog('titles',
            'API responsed with ' . count($result->titles) . ' titles');

        foreach ($result->titles as $title) {

            $title_info = Title::firstOrNew([
                'corporationID' => $this->corporationID,
                'titleID'       => $title->titleID]);

            $title_info->fill([
                'titleName'             => $title->titleName,
                'roles'                 => $this->json_roles($title->roles),
                'grantableRoles'        => $this->json_roles($title->grantableRoles),
                'rolesAtHQ'             => $this->json_roles($title->rolesAtHQ),
                'grantableRolesAtHQ'    => $this->json_roles($title->grantableRolesAtHQ),
                'rolesAtBase'           => $this->json_roles($title->rolesAtBase),
                'grantableRolesAtBase'  => $this->json_roles($title->grantableRolesAtBase),
                'rolesAtOther'          => $this->json_roles($title->rolesAtOther),
                'grantableRolesAtOther' => $this->json_roles($title->grantableRolesAtOther)
            ]);

            $title_info->save();
        }

        return;
    }

    /**
     * Returns a Json Encoded array of Title Information
     *
     * @param $roles
     *
     * @return string
     */
    public function json_roles($roles)
    {

        $result = json_encode(array_map(function ($role) {

            return [
                'id'          => $role->roleID,
                'name'        => $role->roleName,
                'description' => $role->roleDescription
            ];

        }, (array)$roles));

        return $result;
    }
}
