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

namespace Seat\Eveapi\Tests\Resources\Esi\Character;

use Illuminate\Http\Resources\Json\JsonResource;

/**
 * Class Info.
 * @package Seat\Eveapi\Tests\Resources\Esi\Character
 */
class InfoResource extends JsonResource
{
    /**
     * @param \Illuminate\Http\Request $request
     * @return array
     */
    public function toArray($request)
    {
        return [
            'alliance_id'     => $this->when($this->alliance_id !== null, $this->alliance_id),
            'ancestry_id'     => $this->when($this->ancestry_id !== null, $this->ancestry_id),
            'birthday'        => $this->birthday,
            'bloodline_id'    => $this->bloodline_id,
            'corporation_id'  => $this->corporation_id,
            'description'     => $this->when($this->description !== null, $this->description),
            'faction_id'      => $this->when($this->faction_id !== null, $this->faction_id),
            'gender'          => $this->gender,
            'name'            => $this->name,
            'race_id'         => $this->race_id,
            'security_status' => $this->when($this->security_status !== null, $this->security_status),
            'title'           => $this->when($this->title !== null, $this->title),
        ];
    }
}
