<?php

/*
 * This file is part of SeAT
 *
 * Copyright (C) 2015, 2016, 2017  Leon Jacobs
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

namespace Seat\Eveapi\Api\Account;

use Seat\Eveapi\Api\Base;
use Seat\Eveapi\Models\Account\ApiKeyInfo as ApiKeyInfoModel;
use Seat\Eveapi\Models\Account\ApiKeyInfoCharacters;

/**
 * Class APIKeyInfo.
 * @package Seat\Eveapi\Api\Account
 */
class APIKeyInfo extends Base
{
    /**
     * Run the Update.
     *
     * @return mixed|void
     */
    public function call()
    {

        $result = $this->setScope('account')
            ->getPheal()
            ->APIKeyInfo();

        $key_info = ApiKeyInfoModel::firstOrNew([
            'keyID' => $this->api_info->key_id, ]);

        $key_info->fill([
            'accessMask' => $result->key->accessMask,
            'type'       => $result->key->type,
            'expires'    => strlen($result->key->expires) > 0 ?
                $result->key->expires : null,
        ]);

        $key_info->save();

        // Lets process the characters for this API
        // Key. We need to be aware of the fact that it
        // is possible for characters to move around.
        foreach ($result->key->characters as $character) {

            $character_info = ApiKeyInfoCharacters::firstOrNew([
                'keyID'       => $this->api_info->key_id,
                'characterID' => $character->characterID, ]);

            $character_info->fill([
                'characterName'   => $character->characterName,
                'corporationID'   => $character->corporationID,
                'corporationName' => $character->corporationName,
            ]);

            $character_info->save();

        }

        // Cleanup Characters no longer on this key
        ApiKeyInfoCharacters::where('keyID', $this->api_info->key_id)
            ->whereNotIn('characterID', array_map(function ($character) {

                return $character->characterID;

            }, (array) $result->key->characters))
            ->delete();

    }
}
