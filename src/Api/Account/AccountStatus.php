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

namespace Seat\Eveapi\Api\Account;

use Seat\Eveapi\Api\Base;
use Seat\Eveapi\Models\Account\AccountStatus as AccountStatusModel;

/**
 * Class AccountStatus
 * @package Seat\Eveapi\Api\Account
 */
class AccountStatus extends Base
{

    /**
     * Run the Update
     *
     * @return mixed|void
     */
    public function call()
    {

        $result = $this->setScope('account')
            ->getPheal()
            ->AccountStatus();

        $account_status = AccountStatusModel::firstOrNew([
            'keyID' => $this->api_info->key_id]);

        $account_status->fill([
            'paidUntil'    => $result->paidUntil,
            'createDate'   => $result->createDate,
            'logonCount'   => $result->logonCount,
            'logonMinutes' => $result->logonMinutes
        ]);

        $account_status->save();

        return;
    }

}
