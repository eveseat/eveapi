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

namespace Seat\Eveapi\Tests\Resources\Esi\Character;

use Illuminate\Http\Resources\Json\JsonResource;

/**
 * Class RoleResource.
 * @package Seat\Eveapi\Tests\Resources\Esi\Character
 */
class RoleResource extends JsonResource
{
    /**
     * @param \Illuminate\Http\Request $request
     * @return string[]
     */
    public function toArray($request)
    {
        return [
            'roles' => $this->corporation_roles->where('scope', 'roles')->sortBy('role')->pluck('role'),
            'roles_at_base' => $this->corporation_roles->where('scope', 'roles_at_base')->sortBy('role')->pluck('role'),
            'roles_at_hq' => $this->corporation_roles->where('scope', 'roles_at_hq')->sortBy('role')->pluck('role'),
            'roles_at_other' => $this->corporation_roles->where('scope', 'roles_at_other')->sortBy('role')->pluck('role'),
        ];
    }
}
