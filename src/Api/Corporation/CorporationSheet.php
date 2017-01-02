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

namespace Seat\Eveapi\Api\Corporation;

use Seat\Eveapi\Api\Base;
use Seat\Eveapi\Models\Corporation\CorporationSheet as CorporationSheetModel;
use Seat\Eveapi\Models\Corporation\CorporationSheetDivision;
use Seat\Eveapi\Models\Corporation\CorporationSheetWalletDivision;

/**
 * Class CorporationSheet.
 * @package Seat\Eveapi\Api\Corporation
 */
class CorporationSheet extends Base
{
    /**
     * Run the Update.
     *
     * @return mixed|void
     */
    public function call()
    {

        $pheal = $this->setScope('corp')
            ->setCorporationID()->getPheal();

        $result = $pheal->CorporationSheet();

        $corporation_sheet = CorporationSheetModel::firstOrNew([
            'corporationID' => $this->corporationID, ]);

        $corporation_sheet->fill([
            'corporationName' => $result->corporationName,
            'ticker'          => $result->ticker,
            'ceoID'           => $result->ceoID,
            'ceoName'         => $result->ceoName,
            'stationID'       => $result->stationID,
            'stationName'     => $result->stationName,
            'description'     => $result->description,
            'url'             => $result->url,
            'allianceID'      => $result->allianceID,
            'factionID'       => $result->factionID,
            'allianceName'    => $result->allianceName,
            'taxRate'         => $result->taxRate,
            'memberCount'     => $result->memberCount,
            'memberLimit'     => $result->memberLimit,
            'shares'          => $result->shares,
            'graphicID'       => $result->logo->graphicID,
            'shape1'          => $result->logo->shape1,
            'shape2'          => $result->logo->shape2,
            'shape3'          => $result->logo->shape3,
            'color1'          => $result->logo->color1,
            'color2'          => $result->logo->color2,
            'color3'          => $result->logo->color3,
        ]);

        $corporation_sheet->save();

        // Update the Divisional Information
        foreach ($result->divisions as $division) {

            $division_info = CorporationSheetDivision::firstOrNew([
                'corporationID' => $this->corporationID,
                'accountKey'    => $division->accountKey,
            ]);

            $division_info->fill([
                'description' => $division->description,
            ]);

            $division_info->save();
        }

        // Update Wallet Divisions Information
        foreach ($result->walletDivisions as $wallet_division) {

            $wallet_division_info = CorporationSheetWalletDivision::firstOrNew([
                'corporationID' => $this->corporationID,
                'accountKey'    => $wallet_division->accountKey, ]);

            $wallet_division_info->fill([
                'description' => $wallet_division->description,
            ]);

            $wallet_division_info->save();

        }

    }
}
