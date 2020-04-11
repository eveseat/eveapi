<?php

/*
 * This file is part of SeAT
 *
 * Copyright (C) 2015 to 2020 Leon Jacobs
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

namespace Seat\Eveapi\Traits;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Arr;
use Seat\Web\Models\Acl\Permission;

/**
 * Trait AuthorizedScope.
 *
 * @package Seat\Eveapi\Traits
 */
trait AuthorizedScope
{
    /**
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @param string $required_permission
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeAuthorized(Builder $query, string $required_permission)
    {
        if (auth()->user()->hasSuperUser())
            return $query;

        $permission = new Permission([
            'title' => $required_permission,
        ]);

        // the permission is a character permission - apply filter on character_id field
        if ($permission->isCharacterScope()) {
            $character_map = collect(Arr::get(auth()->user()->getAffiliationMap(), 'char'));

            // collect only character which has either the requested permission or wildcard
            $character_ids = $character_map->filter(function ($permissions, $key) use ($permission) {
                return in_array('character.*', $permissions) || in_array($permission->title, $permissions);
            })->keys();

            return $query->whereIn(sprintf('%s.%s', $this->getTable(), 'character_id'), $character_ids);
        }

        // the permission is a corporation permission - apply filter on corporation_id field
        if ($permission->isCorporationScope()) {
            $corporation_map = collect(Arr::get(auth()->user()->getAffiliationMap(), 'corp'));

            // collect only corporation which has either the requested permission or wildcard
            $corporation_ids = $corporation_map->filter(function ($permissions, $key) use ($permission) {
                return in_array('corporation.*', $permissions) || in_array($permission->title, $permissions);
            })->keys();

            return $query->whereIn(sprintf('%s.%s', $this->getTable(), 'corporation_id'), $corporation_ids);
        }

        return $query;
    }
}
