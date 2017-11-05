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

use Carbon\Carbon;
use Seat\Eveapi\Api\Base;
use Seat\Eveapi\Models\Corporation\AssetList as AssetListModel;
use Seat\Eveapi\Models\Corporation\AssetListContents;

/**
 * Class AssetList.
 * @package Seat\Eveapi\Api\Corporation
 */
class AssetList extends Base
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

        $result = $pheal->AssetList([
            'flat' => 1,
        ]);

        $this->writeJobLog('assetlist',
            'API responded with ' . count($result->assets) . ' assets');

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

        $assets = (array) $result->assets;

        $itemIDs = [];

        // Store all itemIDs in a an array to lookup
        // items and determine whether their parent
        // is another item or a structure.
        foreach ($assets as $asset) {

            $itemIDs[$asset->itemID] = null;
        }

        $asset_contents = [];

        // Generate an array with all items being
        // content of another item.
        foreach ($assets as $key => $asset) {

            // lookup locationID in our itemID array.
            // If we found an entry, we add it to our array
            // of asset contents to insert it later.
            if (array_key_exists($asset->locationID, $itemIDs)) {

                $asset_contents[$asset->locationID][] = $asset;

            } else {

                // Insert item to our asset table.
                AssetListModel::updateOrCreate([
                    'itemID' => $asset->itemID,
                ],
                [
                    'corporationID' => $this->corporationID,
                    'locationID'    => $asset->locationID,
                    'typeID'        => $asset->typeID,
                    'quantity'      => $asset->quantity,
                    'flag'          => $asset->flag,
                    'singleton'     => $asset->singleton,
                    'rawQuantity'   => isset($asset->rawQuantity) ?
                        $asset->rawQuantity : 0,

                    // Timestamps
                    'created_at'    => Carbon::now()->toDateTimeString(),
                    'updated_at'    => Carbon::now()->toDateTimeString(),
                ]);

            }
        }

        // Process asset contents.
        foreach ($asset_contents as $itemID => $content) {

            $this->add_asset_content(
                $content, $this->corporationID, $itemID);
        }

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

        foreach ($assets as $asset) {

            AssetListContents::updateOrCreate([
                'itemID'            => $asset->itemID,
            ],
            [
                'corporationID'     => $corporationID,
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
                'updated_at'        => Carbon::now()->toDateTimeString(),

            ]);
        }

    }
}
