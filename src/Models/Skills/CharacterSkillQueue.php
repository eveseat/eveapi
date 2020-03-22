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

namespace Seat\Eveapi\Models\Skills;

use Illuminate\Database\Eloquent\Model;
use Seat\Eveapi\Models\Sde\InvType;

/**
 * Class CharacterSkillQueue.
 * @package Seat\Eveapi\Models\Skills
 *
 * @OA\Schema(
 *     description="Character Skill Queue",
 *     title="CharacterSkillQueue",
 *     type="object"
 * )
 *
 * @OA\Property(
 *     type="string",
 *     format="date-time",
 *     property="finish_date",
 *     description="The date-time when the skill training will end"
 * )
 *
 * @OA\Property(
 *     type="string",
 *     format="date-time",
 *     property="start_date",
 *     description="The date-time when the skill training start"
 * )
 *
 * @OA\Property(
 *     type="integer",
 *     property="finished_level",
 *     description="The level at which the skill will be at end of the training"
 * )
 *
 * @OA\Property(
 *     type="integer",
 *     property="queue_position",
 *     description="The position in the queue"
 * )
 *
 * @OA\Property(
 *     type="integer",
 *     property="training_start_sp",
 *     description="The skillpoint amount in the skill when training start"
 * )
 *
 * @OA\Property(
 *     type="integer",
 *     property="level_end_sp",
 *     description="The skillpoint amount earned at end of the level training"
 * )
 *
 * @OA\Property(
 *     type="integer",
 *     property="level_start_sp",
 *     description="The skillpoint amount from which the training level is starting"
 * )
 *
 * @OA\Property(
 *     property="type",
 *     ref="#/components/schemas/InvType"
 * )
 */
class CharacterSkillQueue extends Model
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
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function type()
    {

        return $this->belongsTo(InvType::class, 'skill_id', 'typeID');
    }
}
