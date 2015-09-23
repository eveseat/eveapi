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

namespace Seat\Eveapi\Api\Corporation;

use Seat\Eveapi\Api\Base;
use Seat\Eveapi\Models\CorporationMemberSecurity;
use Seat\Eveapi\Models\CorporationMemberSecurityTitle;

/**
 * Class MemberSecurity
 * @package Seat\Eveapi\Api\Corporation
 */
class MemberSecurity extends Base
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

        $result = $pheal->MemberSecurity();

        foreach ($result->members as $member) {

            // Cleanup the known roles for this character
            CorporationMemberSecurity::where(
                'characterID', $member->characterID)->delete();

            // Add each type of role for this character
            foreach ($member->roles as $role) {

                CorporationMemberSecurity::create([
                    'characterID'   => $member->characterID,
                    'characterName' => $member->name,
                    'roleType'      => 'roles',
                    'roleID'        => $role->roleID,
                    'roleName'      => $role->roleName
                ]);
            }

            foreach ($member->grantableRoles as $role) {

                CorporationMemberSecurity::create([
                    'characterID'   => $member->characterID,
                    'characterName' => $member->name,
                    'roleType'      => 'grantableRoles',
                    'roleID'        => $role->roleID,
                    'roleName'      => $role->roleName
                ]);
            }

            foreach ($member->rolesAtHQ as $role) {

                CorporationMemberSecurity::create([
                    'characterID'   => $member->characterID,
                    'characterName' => $member->name,
                    'roleType'      => 'rolesAtHQ',
                    'roleID'        => $role->roleID,
                    'roleName'      => $role->roleName
                ]);
            }

            foreach ($member->grantableRolesAtHQ as $role) {

                CorporationMemberSecurity::create([
                    'characterID'   => $member->characterID,
                    'characterName' => $member->name,
                    'roleType'      => 'grantableRolesAtHQ',
                    'roleID'        => $role->roleID,
                    'roleName'      => $role->roleName
                ]);
            }

            foreach ($member->rolesAtBase as $role) {

                CorporationMemberSecurity::create([
                    'characterID'   => $member->characterID,
                    'characterName' => $member->name,
                    'roleType'      => 'rolesAtBase',
                    'roleID'        => $role->roleID,
                    'roleName'      => $role->roleName
                ]);
            }

            foreach ($member->grantableRolesAtBase as $role) {

                CorporationMemberSecurity::create([
                    'characterID'   => $member->characterID,
                    'characterName' => $member->name,
                    'roleType'      => 'grantableRolesAtBase',
                    'roleID'        => $role->roleID,
                    'roleName'      => $role->roleName
                ]);
            }

            foreach ($member->rolesAtOther as $role) {

                CorporationMemberSecurity::create([
                    'characterID'   => $member->characterID,
                    'characterName' => $member->name,
                    'roleType'      => 'rolesAtOther',
                    'roleID'        => $role->roleID,
                    'roleName'      => $role->roleName
                ]);
            }

            foreach ($member->grantableRolesAtOther as $role) {

                CorporationMemberSecurity::create([
                    'characterID'   => $member->characterID,
                    'characterName' => $member->name,
                    'roleType'      => 'grantableRolesAtOther',
                    'roleID'        => $role->roleID,
                    'roleName'      => $role->roleName
                ]);
            }

            // Lastly, cleanup and add the titles
            CorporationMemberSecurityTitle::where(
                'characterID', $member->characterID)->delete();

            // Only set titles if the API response had some
            if ($member->titles) {

                foreach ($member->titles as $title) {

                    CorporationMemberSecurityTitle::create([
                        'characterID'   => $member->characterID,
                        'characterName' => $member->name,
                        'titleID'       => $title->titleID,
                        'titleName'     => $title->titleName
                    ]);
                }
            }

        } // Foreach member

        return;
    }
}
