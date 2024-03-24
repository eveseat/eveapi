<?php

/*
 * This file is part of SeAT
 *
 * Copyright (C) 2015 to present Leon Jacobs
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

use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use OpenApi\Attributes as OA;
use Seat\Eveapi\Models\Sde\InvType;
use Seat\Services\Models\ExtensibleModel;
use Seat\Tests\Eveapi\Database\Factories\CharacterSkillFactory;

#[OA\Schema(
    title: 'CharacterSkill',
    description: 'Character Skill',
    properties: [
        new OA\Property(property: 'skillpoints_in_skill', description: 'The amount of skill point actually learned for that skill', type: 'integer'),
        new OA\Property(property: 'trained_skill_level', description: 'The level up to which the skill as been learned', type: 'integer'),
        new OA\Property(property: 'active_skill_level', description: 'The level actually training', type: 'integer'),
        new OA\Property(property: 'type', ref: '#/components/schemas/InvType', description: 'The inventory type information'),
    ],
    type: 'object'
)]
class CharacterSkill extends ExtensibleModel
{
    use HasFactory;

    /**
     * @var array
     */
    protected $hidden = ['id', 'character_id', 'skill_id', 'created_at', 'updated_at'];

    /**
     * @var bool
     */
    protected static $unguarded = true;

    /**
     * @return \Illuminate\Database\Eloquent\Factories\Factory
     */
    protected static function newFactory(): Factory
    {
        return CharacterSkillFactory::new();
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
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function type()
    {

        return $this->belongsTo(InvType::class, 'skill_id', 'typeID');
    }
}
