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

use Carbon\Carbon;
use Seat\Eveapi\Api\Base;
use Seat\Eveapi\Models\Corporation\AssetList as AssetListModel;
use Seat\Eveapi\Models\Corporation\AssetListContents;

/**
 * Class AssetList
 * @package Seat\Eveapi\Api\Corporation
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

        $pheal = $this->setScope('corp')
            ->setCorporationID()->getPheal();

        $result = $pheal->AssetList();

        $this->writeJobLog('assetlist',
            'API responsed with ' . count($result->assets) . ' assets');

        // The caveat of this API call as can be seen here [1] is
        // that the itemID's may change for a number of reasons.
        // Due to this we need to clear out the assets that we
        // have for this character and repopulate them.
        //
        // [1] https://neweden-dev.com/Corporation/Asset_List
        AssetListModel::where(
            'corporationID', $this->corporationID)->delete();
        AssetListContents::where(
            'corporationID', $this->corporationID)->delete();

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
                array_map(function ($entry) {

                    return [
                        'corporationID' => $this->corporationID,
                        'itemID'        => $entry->itemID,
                        'locationID'    => $entry->locationID,
                        'typeID'        => $entry->typeID,
                        'quantity'      => $entry->quantity,
                        'flag'          => $entry->flag,
                        'singleton'     => $entry->singleton,
                        'rawQuantity'   => isset($entry->rawQuantity) ?
                            $entry->rawQuantity : 0,

                        // Timestamps
                        'created_at'    => Carbon::now()->toDateTimeString(),
                        'updated_at'    => Carbon::now()->toDateTimeString()
                    ];

                }, $asset_chunk));

            // If there were any assets derived form the array_map
            // then we can bulk insert it into the table.
            if (count($asset_list) > 0)
                AssetListModel::insert($asset_list);

            // Next we process the assets contents for this chunk
            // of assets data. We iterate over each of the assets
            // to check if there is some contents that we can add
            // to the database.
            foreach ($asset_chunk as $asset) {

                if (isset($asset->contents))
                    $this->add_asset_content(
                        $asset->contents, $this->corporationID, $asset->itemID);

            } // End foreach $asset_chunk

        } // End array_chunk

        return;
    }

    /**
     * Populate the assets table.
     *
     * This function is called recursively if an asset entry has
     * contents.
     *
     * @param      $assets
     * @param      $corporationID
     * @param null $parentAssetItemID
     * @param null $parentItemID
     */
    public function add_asset_content($assets, $corporationID, $parentAssetItemID = null, $parentItemID = null)
    {

        // Prepare a blank array that will be pupulated
        // for the mass insert at the end.
        $asset_contents = [];

        foreach ($assets as $asset) {

            // Check if this asset has contents, if so,
            // recursively call this function to do
            // the population work.
            //
            // The $parentItemID variable relates to the
            // item in the same table. The variable
            // $parentAssetItemID refers to the original
            // asset in the corp assets table.
            if (isset($asset->contents))
                $this->add_asset_content(
                    $asset->contents, $corporationID,
                    $parentAssetItemID, $asset->itemID);

            array_push($asset_contents, [

                'corporationID'     => $corporationID,
                'itemID'            => $asset->itemID,
                'parentAssetItemID' => $parentAssetItemID,
                'parentItemID'      => $parentItemID,
                'typeID'            => $asset->typeID,
                'quantity'          => $asset->quantity,
                'flag'              => $asset->flag,
                'singleton'         => $asset->singleton,
                'rawQuantity'       => isset($asset->rawQuantity) ?
                    $asset->rawQuantity : 0,

                // Timestamps
                'created_at'        => Carbon::now()->toDateTimeString(),
                'updated_at'        => Carbon::now()->toDateTimeString()
            ]);
        }

        // Again, if there is any contents to add, do so.
        if (count($asset_contents) > 0)
            AssetListContents::insert($asset_contents);

        return;
    }
}
