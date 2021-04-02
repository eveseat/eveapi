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
use Seat\Eveapi\Models\Sde\InvType;

/**
 * Class CharacterSkill.
 * @package Seat\Eveapi\Models\Character
 *
 * @OA\Schema(
 *     description="Character Skill",
 *     title="CharacterSkill",
 *     type="object"
 * )
 *
 * @OA\Property(
 *     type="integer",
 *     property="skillpoints_in_skill",
 *     description="The amount of skill point actually learned for that skill"
 * )
 *
 * @OA\Property(
 *     type="integer",
 *     property="trained_skill_level",
 *     description="The level up to which the skill as been learned"
 * )
 *
 * @OA\Property(
 *     type="integer",
 *     property="active_skill_level",
 *     description="The level actually training"
 * )
 *
 * @OA\Property(
 *     property="type",
 *     ref="#/components/schemas/InvType",
 *     description="The inventory type information"
 * )
 */
class CharacterSkill extends Model
{
    /**
     * @var array
     */
    protected $hidden = ['id', 'character_id', 'skill_id', 'created_at', 'updated_at'];

    /**
     * @var bool
     */
    protected static $unguarded = true;

    /**
     * @var array
     */
    protected $casts = [
        'active_skill_level' => 'integer',
        'skill_id' => 'integer',
        'skillpoints_in_skill' => 'integer',
        'trained_skill_level' => 'integer',
    ];

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
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function type()
    {

        return $this->belongsTo(InvType::class, 'skill_id', 'typeID');
    }
}
