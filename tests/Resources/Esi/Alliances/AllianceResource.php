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

namespace Seat\Eveapi\Tests\Resources\Esi\Alliances;

use Illuminate\Http\Resources\Json\JsonResource;

/**
 * Class AllianceResource.
 * @package Seat\Eveapi\Tests\Resources\Esi\Alliances
 */
class AllianceResource extends JsonResource
{
    /**
     * @param \Illuminate\Http\Request $request
     * @return string[]
     */
    public function toArray($request)
    {
        return [
            'creator_corporation_id' => $this->creator_corporation_id,
            'creator_id' => $this->creator_id,
            'date_founded' => carbon($this->date_founded)->toIso8601ZuluString(),
            'executor_corporation_id' => $this->when($this->executor_corporation_id, $this->executor_corporation_id),
            'faction_id' => $this->when($this->faction_id, $this->faction_id),
            'name' => $this->name,
            'ticker' => $this->ticker,
        ];
    }
}
