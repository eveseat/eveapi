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
 * Class SkillsResource.
 * @package Seat\Eveapi\Tests\Resources\Esi\Skills
 */
class SkillsResource extends JsonResource
{
    /**
     * @param \Illuminate\Http\Request $request
     * @return array
     */
    public function toArray($request)
    {
        return [
            'skills' => SkillResource::collection($this->skills),
            'total_sp' => $this->skillpoints->total_sp,
            'unallocated_sp' => $this->when($this->skillpoints->unallocated_sp, $this->skillpoints->unallocated_sp),
        ];
    }
}
