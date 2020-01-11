<?php

/*
 * This file is part of SeAT
 *
 * Copyright (C) 2015, 2016, 2017, 2018, 2019  Leon Jacobs
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
use Illuminate\Database\Eloquent\SoftDeletes;
use Seat\Eveapi\Models\Character\CharacterInfo;
use Seat\Web\Models\User;

/**
 * Class RefreshToken.
 * @package Seat\Eveapi\Models
 *
 * @SWG\Definition(
 *     description="EVE Online SSO Refresh Token",
 *     title="RefreshToken",
 *     type="object"
 * )
 *
 * @SWG\Property(
 *     type="integer",
 *     format="int64",
 *     minimum=90000000,
 *     description="Character ID to which the token is tied",
 *     property="character_id"
 * )
 *
 * @SWG\Property(
 *     type="string",
 *     description="Refresh token hash",
 *     property="refresh_token"
 * )
 *
 * @SWG\Property(
 *     type="array",
 *     description="Scopes granted for this token",
 *     property="scopes",
 *     @SWG\Items(type="string")
 * )
 *
 * @SWG\Property(
 *     type="string",
 *     format="date-time",
 *     description="The datetime UTC when the token expires",
 *     property="expires_on"
 * )
 *
 * @SWG\Property(
 *     type="string",
 *     description="The short life access token",
 *     property="token"
 * )
 *
 * @SWG\Property(
 *     type="string",
 *     format="date-time",
 *     description="The date-time when the token has been created into SeAT",
 *     property="created_at"
 * )
 *
 * @SWG\Property(
 *     type="string",
 *     format="date-time",
 *     description="The date-time when the token has been updated into SeAT",
 *     property="updated_at"
 * )
 *
 * @SWG\Property(
 *     type="string",
 *     format="date-time",
 *     description="The date-time when the token has been disabled",
 *     property="deleted_at"
 * )
 */
class RefreshToken extends Model
{
    use SoftDeletes;

    /**
     * @var array
     */
    protected $casts = [
        'scopes' => 'array',
    ];

    /**
     * @var array
     */
    protected $dates = ['expires_on', 'deleted_at'];

    /**
     * @var string
     */
    protected $primaryKey = 'character_id';

    /**
     * @var array
     */
    protected $fillable = [
        'character_id', 'user_id', 'character_owner_hash', 'refresh_token', 'scopes', 'expires_on', 'token',
    ];

    /**
     * @var bool
     */
    public $incrementing = false;

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
     * @return \Illuminate\Database\Eloquent\Relations\HasOne
     */
    public function character()
    {
        return $this->hasOne(CharacterInfo::class, 'character_id', 'character_id')
            ->withDefault();
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     * @deprecated 4.0.0
     */
    public function user()
    {

        return $this->belongsTo(User::class, 'character_id', 'id');
    }
}
