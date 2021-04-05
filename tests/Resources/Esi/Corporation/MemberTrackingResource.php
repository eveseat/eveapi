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
 * Class MemberTrackingResource.
 * @package Seat\Eveapi\Tests\Resources\Esi\Corporation
 */
class MemberTrackingResource extends JsonResource
{
    /**
     * @param \Illuminate\Http\Request $request
     * @return array
     */
    public function toArray($request)
    {
        return [
            'base_id' => $this->when(! is_null($this->base_id), $this->base_id),
            'character_id' => $this->character_id,
            'location_id' => $this->when(! is_null($this->location_id), $this->location_id),
            'logoff_date' => $this->when(! is_null($this->logoff_date), carbon($this->logoff_date)->toIso8601ZuluString()),
            'logon_date' => $this->when(! is_null($this->logon_date), carbon($this->logon_date)->toIso8601ZuluString()),
            'ship_type_id' => $this->when(! is_null($this->ship_type_id), $this->ship_type_id),
            'start_date' => $this->when(! is_null($this->start_date), carbon($this->start_date)->toIso8601ZuluString()),
        ];
    }
}
