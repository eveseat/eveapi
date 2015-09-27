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

namespace Seat\Eveapi\Api\Character;

use Carbon\Carbon;
use Seat\Eveapi\Api\Base;
use Seat\Eveapi\Models\Character\AssetList as AssetListModel;
use Seat\Eveapi\Models\Character\AssetListContents;

/**
 * Class AssetList
 * @package Seat\Eveapi\Api\Character
 */
class AssetList extends Base
{

    /**
     * Run the Update
     *
     * @return mixed|void
     */
    public function call()
    {

        $pheal = $this->setScope('char')->getPheal();

        foreach ($this->api_info->characters as $character) {

            $result = $pheal->AssetList([
                'characterID' => $character->characterID]);

            // The caveat of this API call as can be seen here [1] is
            // that the itemID's may change for a number of reasons.
            // Due to this we need to clear out the assets that we
            // have for this character and repopulate them.
            //
            // [1] https://neweden-dev.com/Character/Asset_List
            AssetListModel::where(
                'characterID', $character->characterID)->delete();
            AssetListContents::where(
                'characterID', $character->characterID)->delete();

            // We take the resuls and chunk it up into parts of 1000
            // entries. For every 1000 entries we bulk insert the
            // assets and the asset contents into the database.
            foreach (array_chunk((array)$result->assets, 1000) as $asset_chunk) {

                // Take the chunked array and map the fields to our
                // asset_list variable. This variable is filtered
                // for empty value so that we can prevent a case
                // where a bulk insert fail because of an empty
                // data array variable.
                $asset_list = array_filter(
                    array_map(function ($entry) use ($character) {

                        return [
                            'characterID' => $character->characterID,
                            'itemID'      => $entry->itemID,
                            'locationID'  => $entry->locationID,
                            'typeID'      => $entry->typeID,
                            'quantity'    => $entry->quantity,
                            'flag'        => $entry->flag,
                            'singleton'   => $entry->singleton,
                            'rawQuantity' => isset($entry->rawQuantity) ?
                                $entry->rawQuantity : 0,

                            // Timestamps
                            'created_at'  => Carbon::now()->toDateTimeString(),
                            'updated_at'  => Carbon::now()->toDateTimeString()
                        ];

                    }, $asset_chunk));

                // If there were any assets derived form the array_map
                // then we can bulk insert it into the table.
                if (count($asset_list) > 0)
                    AssetListModel::insert($asset_list);

                // Next we process the assets contents for this chunk
                // of assets data. We need to iterate over each of
                // the assets to check if there is some contents
                // that we can add to the database
                foreach ($asset_chunk as $asset) {

                    if (isset($asset->contents)) {

                        $asset_contents = array_filter(
                            array_map(function ($entry) use ($character, $asset) {

                                return [
                                    'characterID' => $character->characterID,
                                    'itemID'      => $asset->itemID,
                                    'typeID'      => $entry->typeID,
                                    'quantity'    => $entry->quantity,
                                    'flag'        => $entry->flag,
                                    'singleton'   => $entry->singleton,
                                    'rawQuantity' => isset($entry->rawQuantity) ?
                                        $entry->rawQuantity : 0,

                                    // Timestamps
                                    'created_at'  => Carbon::now()->toDateTimeString(),
                                    'updated_at'  => Carbon::now()->toDateTimeString()
                                ];

                            }, (array)$asset->contents)
                        );

                        // Again, if there is any contents to add, do so.
                        if (count($asset_contents) > 0)
                            AssetListContents::insert($asset_contents);

                    }

                } // End foreach $asset_chunk
            } // End array_chunk
        }

        return;
    }
}
