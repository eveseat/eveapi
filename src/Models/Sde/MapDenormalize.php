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

namespace Seat\Eveapi\Models\Sde;

use Illuminate\Database\Eloquent\Model;
use Seat\Eveapi\Models\Sovereignty\SovereigntyMap;
use Seat\Eveapi\Traits\IsReadOnly;

/**
 * Class MapDenormalize.
 * @package Seat\Eveapi\Models\Sde
 *
 * @SWG\Definition(
 *     description="Map Denormalize",
 *     title="MapDenormalize",
 *     type="object"
 * )
 *
 * @SWG\Property(
 *     type="integer",
 *     property="itemID",
 *     description="The entity ID"
 * )
 *
 * @SWG\Property(
 *     type="integer",
 *     property="typeID",
 *     description="The type of the entity"
 * )
 *
 * @SWG\Property(
 *     type="integer",
 *     property="groupID",
 *     description="The group to which the entity is related"
 * )
 *
 * @SWG\Property(
 *     type="integer",
 *     property="solarSystemID",
 *     description="The system to which the entity is attached"
 * )
 *
 * @SWG\Property(
 *     type="integer",
 *     property="constellationID",
 *     description="The constellation to which the entity is attached"
 * )
 *
 * @SWG\Property(
 *     type="integer",
 *     property="regionID",
 *     description="The region to which the entity is attached"
 * )
 *
 * @SWG\Property(
 *     type="integer",
 *     property="orbitID",
 *     description="The orbit to which the entity is depending"
 * )
 *
 * @SWG\Property(
 *     type="number",
 *     format="double",
 *     property="x",
 *     description="x position on the map"
 * )
 *
 * @SWG\Property(
 *     type="number",
 *     format="double",
 *     property="y",
 *     description="y position on the map"
 * )
 *
 * @SWG\Property(
 *     type="number",
 *     format="double",
 *     property="z",
 *     description="z position on the map"
 * )
 *
 * @SWG\Property(
 *     type="number",
 *     format="double",
 *     property="radius",
 *     description="The radius of the entity"
 * )
 *
 * @SWG\Property(
 *     type="string",
 *     property="itemName",
 *     description="The entity name"
 * )
 *
 * @SWG\Property(
 *     type="number",
 *     format="double",
 *     property="security",
 *     description="The security status of the system to which entity is attached"
 * )
 *
 * @SWG\Property(
 *     type="integer",
 *     property="celestialIndex",
 * )
 *
 * @SWG\Property(
 *     type="integer",
 *     property="orbitIndex"
 * )
 */
class MapDenormalize extends Model
{
    use IsReadOnly;

    /**
     * @var bool
     */
    public $incrementing = false;

    /**
     * @var string
     */
    protected $table = 'mapDenormalize';

    /**
     * @var string
     */
    protected $primaryKey = 'itemID';

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function type()
    {

        return $this->belongsTo(InvType::class, 'typeID', 'typeID');
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasOne
     */
    public function sovereignty()
    {

        return $this->hasOne(SovereigntyMap::class, 'system_id', 'itemID');
    }

    /**
     * @return int
     */
    public function getStructureIdAttribute()
    {
        return $this->structure_id;
    }

    /**
     * @return int
     */
    public function getSolarSystemIdAttribute()
    {
        if (! is_null($this->solarSystemID))
            return $this->solarSystemID;

        return $this->itemID;
    }

    /**
     * @return int
     */
    public function getTypeIdAttribute()
    {
        return $this->typeID;
    }

    /**
     * @return string
     */
    public function getNameAttribute()
    {
        return $this->itemName;
    }
}
