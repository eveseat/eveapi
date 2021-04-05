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
 * Class SkillQueueResource.
 * @package Seat\Eveapi\Tests\Resources\Esi\Skills
 */
class SkillQueueResource extends JsonResource
{
    /**
     * @param \Illuminate\Http\Request $request
     * @return array
     */
    public function toArray($request)
    {
        return [
            'finish_date' => $this->when(! is_null($this->finish_date), carbon($this->finish_date)->toIso8601ZuluString()),
            'finished_level' => $this->finished_level,
            'level_end_sp' => $this->when(! is_null($this->level_end_sp), $this->level_end_sp),
            'level_start_sp' => $this->when(! is_null($this->level_start_sp), $this->level_start_sp),
            'queue_position' => $this->queue_position,
            'skill_id' => $this->skill_id,
            'start_date' => $this->when(! is_null($this->start_date), carbon($this->start_date)->toIso8601ZuluString()),
            'training_start_sp' => $this->when(! is_null($this->training_start_sp), $this->training_start_sp),
        ];
    }
}
