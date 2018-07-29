<?php

/*
 * This file is part of SeAT
 *
 * Copyright (C) 2015, 2016, 2017, 2018  Leon Jacobs
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

namespace Seat\Eveapi\Models\Bookmarks;

use Illuminate\Database\Eloquent\Model;
use Seat\Eveapi\Models\Sde\MapDenormalize;
use Seat\Eveapi\Traits\HasCompositePrimaryKey;

/**
 * Class CorporationBookmark.
 * @package Seat\Eveapi\Models\Bookmarks
 *
 * @SWG\Definition(
 *     description="Corporation Bookmark",
 *     title="CorporationBookmark",
 *     type="object"
 * )
 *
 * @SWG\Property(
 *     type="integer",
 *     format="int64",
 *     property="bookmark_id",
 *     description="The bookmark identifier"
 * )
 *
 * @SWG\Property(
 *     type="integer",
 *     format="int64",
 *     property="folder_id",
 *     description="The folder ID into which the bookmark resides"
 * )
 *
 * @SWG\Property(
 *     type="string",
 *     property="folder_name",
 *     description="The folder name into which the bookmark resides"
 * )
 *
 * @SWG\Property(
 *     property="system",
 *     ref="#/definitions/MapDenormalize"
 * )
 *
 * @SWG\Property(
 *     type="string",
 *     format="date-time",
 *     property="created",
 *     description="The bookmark creation date"
 * )
 *
 * @SWG\Property(
 *     type="string",
 *     property="label",
 *     description="The bookmark label"
 * )
 *
 * @SWG\Property(
 *     type="string",
 *     property="notes",
 *     description="A note attached to the bookmark"
 * )
 *
 * @SWG\Property(
 *     type="integer",
 *     property="location_id",
 *     description="The system ID where the bookmark is"
 * )
 *
 * @SWG\Property(
 *     type="integer",
 *     format="int64",
 *     property="creator_id",
 *     description="The character who created the bookmark"
 * )
 *
 * @SWG\Property(
 *     type="integer",
 *     format="int64",
 *     property="item_id",
 *     description="The in-game item on which the bookmark has been took"
 * )
 *
 * @SWG\Property(
 *     type="integer",
 *     property="type_id",
 *     description="The type of them item"
 * )
 *
 * @SWG\Property(
 *     type="number",
 *     format="double",
 *     property="x",
 *     description="The x position on the map"
 * )
 *
 * @SWG\Property(
 *     type="number",
 *     format="double",
 *     property="y",
 *     description="The y position on the map"
 * )
 *
 * @SWG\Property(
 *     type="number",
 *     format="double",
 *     property="z",
 *     description="The z position on the map"
 * )
 *
 * @SWG\Property(
 *     type="integer",
 *     format="int64",
 *     property="map_id",
 *     description="The map to which the bookmark is referencing"
 * )
 */
class CorporationBookmark extends Model
{
    use HasCompositePrimaryKey;

    /**
     * @var bool
     */
    protected static $unguarded = true;

    /**
     * @var array
     */
    protected $primaryKey = ['corporation_id', 'bookmark_id'];

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function folder()
    {

        return $this->belongsTo(CorporationBookmarkFolder::class, 'folder_id', 'folder_id')
            ->withDefault([
                'corporation_id' => $this->corporation_id,
                'folder_id'      => 0,
                'name'           => 'None',
            ]);
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasOne
     */
    public function system()
    {

        return $this->hasOne(MapDenormalize::class, 'itemID', 'location_id');
    }
}
