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

namespace Seat\Eveapi\Models\Character;

use Illuminate\Database\Eloquent\Model;
use Seat\Eveapi\Models\Universe\UniverseName;
use Seat\Eveapi\Traits\HasCompositePrimaryKey;

/**
 * Class CharacterCorporationHistory.
 * @package Seat\Eveapi\Models\Character
 *
 * @SWG\Definition(
 *     description="Character Corporation History",
 *     title="CharacterCorporationHistory",
 *     type="object"
 * )
 *
 * @SWG\Property(
 *     type="string",
 *     format="date-time",
 *     property="start_date",
 *     description="The date-time from which the character was inside the corporation"
 * )
 *
 * @SWG\Property(
 *     type="integer",
 *     format="int64",
 *     property="corporation_id",
 *     description="The corporation ID into which the character was"
 * )
 *
 * @SWG\Property(
 *     type="boolean",
 *     property="is_deleted",
 *     description="True if the corporation has been close"
 * )
 *
 * @SWG\Property(
 *     type="integer",
 *     property="record_id",
 *     description="Sorting key"
 * )
 *
 * @SWG\Property(
 *     type="string",
 *     format="date-time",
 *     property="created_at",
 *     description="The date-time when record has been created into SeAT"
 * )
 *
 * @SWG\Property(
 *     type="string",
 *     format="date-time",
 *     property="updated_at",
 *     description="The date-time when record has been updated into SeAT"
 * )
 */
class CharacterCorporationHistory extends Model
{

    use HasCompositePrimaryKey;

    /**
     * @var bool
     */
    protected static $unguarded = true;

    /**
     * @var string
     */
    protected $primaryKey = ['character_id', 'record_id'];

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasOne
     */
    public function corporation()
    {
        return $this->hasOne(UniverseName::class, 'entity_id', 'corporation_id')
            ->withDefault([
                'entity_id' => $this->corporation_id,
                'category'  => 'corporation',
                'name'      => trans('web::seat.unknown'),
            ]);
    }
}
