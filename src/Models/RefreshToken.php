<?php

/*
 * This file is part of SeAT
 *
 * Copyright (C) 2015, 2016, 2017, 2018  Leon Jacobs
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

namespace Seat\Eveapi\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Seat\Web\Models\User;

/**
 * Class RefreshToken.
 * @package Seat\Eveapi\Models
 */
class RefreshToken extends Model
{

    /**
     * @var array
     */
    protected $casts = [
        'scopes' => 'array',
    ];

    /**
     * @var array
     */
    protected $dates = ['expires_on'];

    /**
     * @var string
     */
    protected $primaryKey = 'character_id';

    /**
     * @var array
     */
    protected $fillable = ['character_id', 'refresh_token', 'scopes', 'expires_on', 'token'];

    /**
     * Only return a token value if it is not already
     * considered expired.
     *
     * @param $value
     *
     * @return mixed
     */
    public function getTokenAttribute($value)
    {

        if ($this->expires_on->gt(Carbon::now()))
            return $value;

        return null;
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function user()
    {

        return $this->belongsTo(User::class, 'id', 'character_id');
    }
}
