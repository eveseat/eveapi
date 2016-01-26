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
use Seat\Eveapi\Models\Corporation\MemberTracking as MemberTrackingModel;

/**
 * Class MemberTracking
 * @package Seat\Eveapi\Api\Corporation
 */
class MemberTracking extends Base
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

        $result = $pheal->MemberTracking(['extended' => 1]);

        foreach ($result->members as $member) {

            $member_info = MemberTrackingModel::firstOrNew([
                'corporationID' => $this->corporationID,
                'characterID'   => $member->characterID]);

            $member_info->fill([
                'name'           => $member->name,
                'startDateTime'  => $member->startDateTime,
                'baseID'         => $member->baseID,
                'base'           => $member->base,
                'title'          => $member->title,
                'logonDateTime'  => $member->logonDateTime,
                'logoffDateTime' => $member->logoffDateTime,
                'locationID'     => $member->locationID,
                'location'       => $member->location,
                'shipTypeID'     => $member->shipTypeID,
                'shipType'       => $member->shipType,
                'roles'          => $member->roles,
                'grantableRoles' => $member->grantableRoles
            ]);

            $member_info->save();
        }

        // Cleanup members no longer in this corporation
        MemberTrackingModel::where('corporationID', $this->corporationID)
            ->whereNotIn('characterID', array_map(function ($member) {

                return $member->characterID;

            }, (array)$result->members))
            ->delete();

        return;
    }
}
