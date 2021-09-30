<?php

/*
 * This file is part of SeAT
 *
 * Copyright (C) 2015 to 2021 Leon Jacobs
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

namespace Seat\Eveapi\Mapping\Characters;

use Seat\Eveapi\Mapping\DataMapping;

/**
 * Class SkillMapping.
 *
 * @package Seat\Eveapi\Mapping\Characters
 */
class SkillMapping extends DataMapping
{
    /**
     * @var array
     */
    protected static $mapping = [
        'skill_id'             => 'skill_id',
        'skillpoints_in_skill' => 'skillpoints_in_skill',
        'trained_skill_level'  => 'trained_skill_level',
        'active_skill_level'   => 'active_skill_level',
    ];
}
