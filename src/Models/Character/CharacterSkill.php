<?php

/*
 * This file is part of SeAT
 *
 * Copyright (C) 2015, 2016, 2017, 2018, 2019  Leon Jacobs
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
use Seat\Eveapi\Models\Sde\InvType;
use Seat\Eveapi\Traits\HasCompositePrimaryKey;

/**
 * Class CharacterSkill.
 * @package Seat\Eveapi\Models\Character
 *
 * @SWG\Definition(
 *     description="Character Skill",
 *     title="CharacterSkill",
 *     type="object"
 * )
 *
 * @SWG\Property(
 *     type="integer",
 *     property="skill_id",
 *     description="The skill inventory type ID"
 * )
 *
 * @SWG\Property(
 *     type="integer",
 *     property="skillpoints_in_skill",
 *     description="The amount of skill point actually learned for that skill"
 * )
 *
 * @SWG\Property(
 *     type="integer",
 *     property="trained_skill_level",
 *     description="The level up to which the skill as been learned"
 * )
 *
 * @SWG\Property(
 *     type="integer",
 *     property="active_skill_level",
 *     description="The level actually training"
 * )
 *
 * @SWG\Property(
 *     property="type",
 *     ref="#/definitions/InvType",
 *     description="The inventory type information"
 * )
 */
class CharacterSkill extends Model
{
    use HasCompositePrimaryKey;

    /**
     * @var bool
     */
    protected static $unguarded = true;

    /**
     * @var bool
     */
    public $incrementing = false;

    /**
     * @var array
     */
    protected $primaryKey = ['character_id', 'skill_id'];

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function type()
    {

        return $this->belongsTo(InvType::class, 'skill_id', 'typeID');
    }
}
