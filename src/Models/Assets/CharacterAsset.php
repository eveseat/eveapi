<?php

/*
 * This file is part of SeAT
 *
 * Copyright (C) 2015 to 2022 Leon Jacobs
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

use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use OpenApi\Attributes as OA;
use Seat\Eveapi\Models\Character\CharacterInfo;
use Seat\Eveapi\Models\Sde\InvGroup;
use Seat\Eveapi\Models\Sde\InvType;
use Seat\Eveapi\Models\Sde\SolarSystem;
use Seat\Eveapi\Models\Universe\UniverseStation;
use Seat\Eveapi\Models\Universe\UniverseStructure;
use Seat\Tests\Eveapi\Database\Factories\CharacterAffiliationFactory;

#[OA\Schema(
    title: 'CharacterAsset',
    description: 'Character Asset',
    properties: [
        new OA\Property(property: 'item_id', description: 'The item identifier', type: 'integer', format: 'int64'),
        new OA\Property(property: 'quantity', description: 'The item quantity', type: 'integer'),
        new OA\Property(property: 'location_id', description: 'The place of the item', type: 'integer', format: 'int64'),
        new OA\Property(property: 'location_type', description: 'The location qualifier', type: 'string', enum: ['station', 'solar_system', 'other']),
        new OA\Property(property: 'location_flag', description: 'The location flag'),
        new OA\Property(property: 'is_singleton', description: 'True if the item is not stacked', type: 'boolean'),
        new OA\Property(property: 'x', description: 'The x coordinate if the item is in space', type: 'number', format: 'double'),
        new OA\Property(property: 'y', description: 'The y coordinate if the item is in space', type: 'number', format: 'double'),
        new OA\Property(property: 'z', description: 'The z coordinate if the item is in space', type: 'number', format: 'double'),
        new OA\Property(property: 'map_id', description: 'The map identifier into which items is located', type: 'integer'),
        new OA\Property(property: 'map_name', description: 'The map name of the system where the item resides', type: 'string'),
        new OA\Property(property: 'name', description: 'The name of the asset (ie: a ship name)', type: 'string'),
        new OA\Property(property: 'type', ref: '#/components/schemas/InvType'),
    ],
    type: 'object'
)]
class CharacterAsset extends Model
{
    use HasFactory;

    /**
     * @var array
     */
    protected $hidden = ['character_id', 'type_id', 'created_at', 'updated_at'];

    /**
     * @var array
     */
    protected $casts = [
        'is_singleton' => 'boolean',
    ];

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
     * @return \Illuminate\Database\Eloquent\Factories\Factory
     */
    protected static function newFactory(): Factory
    {
        return CharacterAffiliationFactory::new();
    }

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
     * @return \Illuminate\Database\Eloquent\Relations\HasOne
     */
    public function type()
    {
        return $this->hasOne(InvType::class, 'typeID', 'type_id')
            ->withDefault(function ($type) {
                $group = new InvGroup();
                $group->groupName = 'Unknown';

                $type->typeName = trans('web::seat.unknown');
                $type->group = $group;
            });
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function container()
    {

        return $this->belongsTo(CharacterAsset::class, 'location_id', 'item_id')
            ->withDefault();
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
    public function solar_system()
    {
        return $this->hasOne(SolarSystem::class, 'system_id', 'location_id')
            ->withDefault();
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasOne
     */
    public function station()
    {
        return $this->hasOne(UniverseStation::class, 'station_id', 'location_id')
            ->withDefault([
                'name' => trans('web::seat.unknown'),
            ]);
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasOne
     */
    public function structure()
    {
        return $this->hasOne(UniverseStructure::class, 'structure_id', 'location_id')
            ->withDefault([
                'name' => trans('web::seat.unknown'),
            ]);
    }
}
