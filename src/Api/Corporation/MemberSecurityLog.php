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
use Seat\Eveapi\Models\CorporationMemberSecurityLog;
use Seat\Eveapi\Traits\Utils;

/**
 * Class MemberSecurityLog
 * @package Seat\Eveapi\Api\Corporation
 */
class MemberSecurityLog extends Base
{

    use Utils;

    /**
     * Run the Update
     *
     * @return mixed|void
     */
    public function call()
    {

        $pheal = $this->setScope('corp')
            ->setCorporationID()->getPheal();

        $result = $pheal->MemberSecurityLog();

        foreach ($result->roleHistory as $log_entry) {

            // Log entries are defined by a unique hash
            $hash = $this->hash_transaction(
                $log_entry->characterID,
                $log_entry->changeTime,
                $log_entry->characterName,
                $log_entry->roleLocationType);

            // If we have the log entry, move to the next
            if (CorporationMemberSecurityLog::where('hash', $hash)->first())
                continue;

            CorporationMemberSecurityLog::create([
                'hash'             => $hash,
                'corporationID'    => $this->corporationID,
                'characterID'      => $log_entry->characterID,
                'characterName'    => $log_entry->characterName,
                'changeTime'       => $log_entry->changeTime,
                'issuerID'         => $log_entry->issuerID,
                'issuerName'       => $log_entry->issuerName,
                'roleLocationType' => $log_entry->roleLocationType,
                'oldRoles'         => $this->json_roles($log_entry->oldRoles),
                'newRoles'         => $this->json_roles($log_entry->newRoles)
            ]);

        }

        return;
    }

    /**
     * Json Encodes Roles from the Member Security Log
     *
     * @param $roles
     *
     * @return string
     */
    public function json_roles($roles)
    {

        $result = json_encode(array_map(function ($role) {

            return [$role->roleID => $role->roleName];

        }, (array)$roles));

        return $result;
    }
}
