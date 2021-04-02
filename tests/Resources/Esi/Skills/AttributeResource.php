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
 *  You should have received a copy of the GNU General Public License along
 *  with this program; if not, write to the Free Software Foundation, Inc.,
 *  51 Franklin Street, Fifth Floor, Boston, MA 02110-1301 USA.
 */

namespace Seat\Eveapi\Tests\Resources\Esi\Skills;

use Illuminate\Http\Resources\Json\JsonResource;

/**
 * Class AttributeResource.
 * @package Seat\Eveapi\Tests\Resources\Esi\Skills
 */
class AttributeResource extends JsonResource
{
    /**
     * @param \Illuminate\Http\Request $request
     * @return array
     */
    public function toArray($request)
    {
        return [
            'accrued_remap_cooldown_date' => $this->when(! is_null($this->accrued_remap_cooldown_date), carbon($this->accrued_remap_cooldown_date)->toIso8601ZuluString()),
            'bonus_remaps' => $this->when(! is_null($this->bonus_remaps), $this->bonus_remaps),
            'charisma' => $this->charisma,
            'intelligence' => $this->intelligence,
            'last_remap_date' => $this->when(! is_null($this->last_remap_date), carbon($this->last_remap_date)->toIso8601ZuluString()),
            'memory' => $this->memory,
            'perception' => $this->perception,
            'willpower' => $this->willpower
        ];
    }
}
