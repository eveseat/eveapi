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
use Seat\Eveapi\Models\CorporationShareholder;

/**
 * Class Shareholders
 * @package Seat\Eveapi\Api\Corporation
 */
class Shareholders extends Base
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

        $result = $pheal->Shareholders();

        // Process shareholding Characters
        foreach ($result->characters as $character) {

            $share_info = CorporationShareholder::firstOrNew([
                'corporationID'   => $this->corporationID,
                'shareholderType' => 'character',
                'shareholderID'   => $character->shareholderID]);

            $share_info->fill([
                'shareholderName'            => $character->shareholderName,
                'shareholderCorporationID'   => $character->shareholderCorporationID,
                'shareholderCorporationName' => $character->shareholderCorporationName,
                'shares'                     => $character->shares
            ]);

            $share_info->save();
        }

        // Cleanup Old Shareholding Characters
        CorporationShareholder::where('corporationID', $this->corporationID)
            ->where('shareholderType', 'character')
            ->whereNotIn('shareholderID', array_map(function ($character) {

                return $character->shareholderID;

            }, (array)$result->characters))
            ->delete();

        // Process Shareholding Corporations
        foreach ($result->corporations as $corporation) {

            $share_info = CorporationShareholder::firstOrNew([
                'corporationID'   => $this->corporationID,
                'shareholderType' => 'corporation',
                'shareholderID'   => $corporation->shareholderID]);

            $share_info->fill([
                'shareholderName' => $character->shareholderName,
                'shares'          => $character->shares
            ]);

            $share_info->save();
        }

        // Cleanup Old Shareholding Corporations
        CorporationShareholder::where('corporationID', $this->corporationID)
            ->where('shareholderType', 'corporation')
            ->whereNotIn('shareholderID', array_map(function ($corporation) {

                return $corporation->shareholderID;

            }, (array)$result->corporations))
            ->delete();

        return;
    }
}
