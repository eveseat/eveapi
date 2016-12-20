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
use Seat\Eveapi\Models\Corporation\MemberSecurity as MemberSecurityModel;
use Seat\Eveapi\Models\Corporation\MemberSecurityTitle;

/**
 * Class MemberSecurity
 * @package Seat\Eveapi\Api\Corporation
 */
class MemberSecurity extends Base
{

    /**
     * The member that is currently in scope
     *
     * @var
     */
    protected $member;

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

        $this->writeJobLog('membersecurity',
            'API responsed with ' . count($result->members) . ' members');

        foreach ($result->members as $member) {

            // Update the membe that is currently in scope
            $this->member = $member;

            // Cleanup the known roles for this character
            MemberSecurityModel::where(
                'characterID', $member->characterID)->delete();

            // Write the new roles
            $this->writeEntry($member->roles, 'roles');
            $this->writeEntry($member->grantableRoles, 'grantableRoles');
            $this->writeEntry($member->rolesAtHQ, 'rolesAtHQ');
            $this->writeEntry($member->grantableRolesAtHQ, 'grantableRolesAtHQ');
            $this->writeEntry($member->rolesAtBase, 'rolesAtBase');
            $this->writeEntry($member->grantableRolesAtBase, 'grantableRolesAtBase');
            $this->writeEntry($member->rolesAtOther, 'rolesAtOther');
            $this->writeEntry($member->grantableRolesAtOther, 'grantableRolesAtOther');

            // Lastly, cleanup and add the titles
            MemberSecurityTitle::where(
                'characterID', $member->characterID)->delete();

            // Only set titles if the API response had some
            if ($member->titles) {

                foreach ($member->titles as $title) {

                    MemberSecurityTitle::create([
                        'corporationID' => $this->getCorporationID(),
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

    /**
     * Write an entry to the MemberSecurity table
     *
     * @param $rawRoles
     * @param $roleType
     */
    private function writeEntry($rawRoles, $roleType)
    {

        // Add each type of role for this character
        foreach ($rawRoles as $role) {

            MemberSecurityModel::create([
                'corporationID' => $this->getCorporationID(),
                'characterID'   => $this->member->characterID,
                'characterName' => $this->member->name,
                'roleType'      => $roleType,
                'roleID'        => $role->roleID,
                'roleName'      => $role->roleName
            ]);
        }
    }
}
