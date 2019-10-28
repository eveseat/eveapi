<?php

/*
 * This file is part of SeAT
 *
 * Copyright (C) 2015 to 2020 Leon Jacobs
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

namespace Seat\Eveapi\Models\Assets;

use Illuminate\Database\Eloquent\Model;
use Seat\Eveapi\Models\Character\CharacterInfo;
use Seat\Eveapi\Models\Sde\InvType;
use Seat\Eveapi\Models\Sde\MapDenormalize;
use Seat\Eveapi\Models\Sde\StaStation;
use Seat\Eveapi\Models\Universe\UniverseStructure;
use Seat\Eveapi\Traits\AuthorizedScope;
use Seat\Eveapi\Traits\CanUpsertIgnoreReplace;

/**
 * Class CharacterAsset.
 * @package Seat\Eveapi\Models\Assets
 *
 * @SWG\Definition(
 *     description="Character Asset",
 *     title="CharacterAsset",
 *     type="object"
 * )
 *
 * @SWG\Property(
 *     type="integer",
 *     format="int64",
 *     property="item_id",
 *     description="The item identifier"
 * )
 *
 * @SWG\Property(
 *     type="integer",
 *     property="type_id",
 *     description="The item inventory type identifier"
 * )
 *
 * @SWG\Property(
 *     type="integer",
 *     format="integer",
 *     property="quantity",
 *     description="The item quantity"
 * )
 *
 * @SWG\Property(
 *     type="integer",
 *     format="int64",
 *     property="location_id",
 *     description="The place of the item"
 * )
 *
 * @SWG\Property(
 *     type="string",
 *     enum={"station", "solar_system", "other"},
 *     property="location_type",
 *     description="The location qualifier"
 * )
 *
 * @SWG\Property(
 *     type="string",
 *     property="location_flag",
 *     description="The location flag"
 * )
 *
 * @SWG\Property(
 *     type="boolean",
 *     property="is_singleton",
 *     description="True if the item is not stacked"
 * )
 *
 * @SWG\Property(
 *     type="number",
 *     format="double",
 *     property="x",
 *     description="The x coordinate if the item is in space"
 * )
 *
 * @SWG\Property(
 *     type="number",
 *     format="double",
 *     property="y",
 *     description="The y coordinate if the item is in space"
 * )
 *
 * @SWG\Property(
 *     type="number",
 *     format="double",
 *     property="z",
 *     description="The z coordinate if the item is in space"
 * )
 *
 * @SWG\Property(
 *     type="integer",
 *     property="map_id",
 *     description="The map identifier into which item is located"
 * )
 *
 * @SWG\Property(
 *     type="string",
 *     property="map_name",
 *     description="The name of the system where the item resides"
 * )
 *
 * @SWG\Property(
 *     property="type",
 *     ref="#/definitions/InvType"
 * )
 */
class CharacterAsset extends Model
{
    use CanUpsertIgnoreReplace;
    use AuthorizedScope;

    /**
     * @var bool
     */
    protected static $unguarded = true;

    /**
     * @var bool
     */
    public $incrementing = false;

    /**
     * @var
     */
    protected $primaryKey = 'item_id';

    /**
     * Provide a rate of the used space based on item capacity and stored item volume.
     * Lets us use this as CharacterAsset->used_volume_rate.
     *
     * @return float
     */
    public function getUsedVolumeRateAttribute()
    {

        if ($this->type->capacity == 0)
            return 0.0;

        return $this->getUsedVolumeAttribute() / $this->type->capacity * 100;
    }

    /**
     * Provide the used space based on stored item volume.
     *
     * @return float
     */
    public function getUsedVolumeAttribute()
    {

        $content = $this->content;

        if (! is_null($content))
            return $content->sum(function ($item) {

                return $item->type->volume;
            });

        return 0.0;
    }

    /**
     * @return mixed
     * @see https://github.com/esi/esi-docs/blob/master/docs/asset_location_id.md
     */
    public function getLocationAttribute()
    {
        switch (true) {
            // asset safety range
            case $this->location_id == 2004:
                return $this->asset_safety;
            // solar system range (including abyssal)
            case $this->location_id >= 30000000 && $this->location_id <= 33000000:
                return $this->system;
            // station range
            case $this->location_id >= 60000000 && $this->location_id <= 64000000:
                return $this->station;
            // citadels range
            case in_array($this->location_flag, ['Hangar', 'AssetSafety']):
                return $this->structure;
            // everything else is an asset
            default:
                return $this->asset;
        }
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasOne
     */
    public function type()
    {

        return $this->hasOne(InvType::class, 'typeID', 'type_id')
            ->withDefault([
                'typeName' => trans('web::seat.unknown'),
            ]);
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function container()
    {

        return $this->belongsTo(CharacterAsset::class, 'item_id', 'location_id');
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function content()
    {

        return $this->hasMany(CharacterAsset::class, 'location_id', 'item_id');
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function character()
    {
        return $this->belongsTo(CharacterInfo::class, 'character_id', 'character_id')
            ->withDefault([
                'name' => trans('web::seat.unknown'),
            ]);
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasOne
     */
    public function asset_safety()
    {
        return $this->hasOne(MapDenormalize::class, 'itemID', 'location_id')
            ->withDefault([
                'itemName' => 'Asset Safety',
            ]);
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasOne
     */
    public function system()
    {
        return $this->hasOne(MapDenormalize::class, 'itemID', 'location_id')
            ->withDefault([
                'itemName' => 'no system',
            ]);
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasOne
     */
    public function station()
    {
        return $this->hasOne(StaStation::class, 'stationID', 'location_id')
            ->withDefault([
                'stationName' => 'fucking SDE',
            ]);
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasOne
     */
    public function structure()
    {
        return $this->hasOne(UniverseStructure::class, 'structure_id', 'location_id')
            ->withDefault([
                'name' => 'unknown structure',
            ]);
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasOne
     */
    public function asset()
    {
        return $this->hasOne(CharacterAsset::class, 'item_id', 'location_id')
            ->withDefault([
                'name' => 'unknown asset',
            ]);
    }
}
