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

namespace Seat\Eveapi\Tests\Resources\Esi\Corporation;

use Illuminate\Http\Resources\Json\JsonResource;

/**
 * Class InfoResource.
 * @package Seat\Eveapi\Tests\Resources\Esi\Corporation
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
            'alliance_id' => $this->when(! is_null($this->alliance_id), $this->alliance_id),
            'ceo_id' => $this->ceo_id,
            'creator_id' => $this->creator_id,
            'date_founded' => $this->when(! is_null($this->date_founded), carbon($this->date_founded)->toIso8601ZuluString()),
            'description' => $this->when(! is_null($this->description), $this->description),
            'faction_id' => $this->when(! is_null($this->faction_id), $this->faction_id),
            'home_station_id' => $this->when(! is_null($this->home_station_id), $this->home_station_id),
            'member_count' => $this->member_count,
            'name' => $this->name,
            'shares' => $this->when(! is_null($this->shares), $this->shares),
            'tax_rate' => $this->tax_rate,
            'ticker' => $this->ticker,
            'url' => $this->when(! is_null($this->url), $this->url),
            'war_eligible' => $this->when($this->war_eligible, $this->war_eligible),
        ];
    }
}
