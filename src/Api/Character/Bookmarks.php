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

use Seat\Eveapi\Api\Base;
use Seat\Eveapi\Models\CharacterBookmark;
use Seat\Eveapi\Models\EveApiKey;

/**
 * Class Bookmarks
 * @package Seat\Eveapi\Api\Character
 */
class Bookmarks extends Base
{

    /**
     * Run the Update
     *
     * @param \Seat\Eveapi\Models\EveApiKey $api_info
     */
    public function call(EveApiKey $api_info)
    {

        foreach ($api_info->characters as $character) {

            $result = $this->setKey(
                $api_info->key_id, $api_info->v_code)
                ->getPheal()
                ->charScope
                ->Bookmarks([
                    'characterID' => $character->characterID]);

            // Process each folder and the bookmarks therein
            foreach ($result->folders as $folder) {

                // Currently a denormalized table is used for both
                // the folder information as well as the bookmarks
                // themselves.
                foreach ($folder->bookmarks as $bookmark) {

                    $bookmark_info = CharacterBookmark::firstOrNew([
                        'characterID' => $character->characterID,
                        'folderID'    => $folder->folderID,
                        'bookmarkID'  => $bookmark->bookmarkID,
                    ]);

                    $bookmark_info->fill([
                        'folderName' => $folder->folderName,
                        'creatorID'  => $bookmark->creatorID,
                        'created'    => $bookmark->created,
                        'itemID'     => $bookmark->itemID,
                        'typeID'     => $bookmark->typeID,
                        'locationID' => $bookmark->locationID,
                        'x'          => $bookmark->x,
                        'y'          => $bookmark->y,
                        'z'          => $bookmark->z,
                        'memo'       => $bookmark->memo,
                        'note'       => $bookmark->note
                    ]);

                    $bookmark_info->save();

                } // Foreach Bookmark

                // Cleanup old bookmarks in this folder
                CharacterBookmark::where('characterID', $character->characterID)
                    ->where('folderID', $folder->folderID)
                    ->whereNotIn('bookmarkID', array_map(function ($bookmark) {

                        return $bookmark->bookmarkID;

                    }, (array)$folder->bookmarks))
                    ->delete();

            } // Foreach Folder

            // Cleanup old folders
            CharacterBookmark::where('characterID', $character->characterID)
                ->whereNotIn('folderID', array_map(function ($folder) {

                    return $folder->folderID;

                }, (array)$result->folders))
                ->delete();
        }

        return;
    }
}
