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

namespace Seat\Eveapi\Models\Alliances;

use Illuminate\Database\Eloquent\Model;

/**
 * Class Alliance.
 * @package Seat\Eveapi\Models\Alliances
 */
class Alliance extends Model
{
    /**
     * @var array
     */
    protected $hidden = ['created_at', 'updated_at'];

    /**
     * @var bool
     */
    protected static $unguarded = true;

    /**
     * @var bool
     */
    public $incrementing = false;

    /**
     * @var string
     */
    protected $primaryKey = 'alliance_id';

    /**
     * @param $value
     */
    public function setDateFoundedAttribute($value)
    {
        $this->attributes['date_founded'] = is_null($value) ? null : carbon($value);
    }
}
