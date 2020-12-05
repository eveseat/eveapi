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

namespace Seat\Eveapi\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Seat\Eveapi\Models\Character\CharacterAffiliation;
use Seat\Eveapi\Models\Character\CharacterInfo;
use Seat\Web\Models\User;

/**
 * Class RefreshToken.
 * @package Seat\Eveapi\Models
 *
 * @OA\Schema(
 *     description="EVE Online SSO Refresh Token",
 *     title="RefreshToken",
 *     type="object"
 * )
 *
 * @OA\Property(
 *     type="integer",
 *     format="int64",
 *     minimum=90000000,
 *     description="Character ID to which the token is tied",
 *     property="character_id"
 * )
 * @OA\Property(
 *     type="integer",
 *     format="uint8",
 *     minimum=1,
 *     description="Refresh Token SSO Version",
 *     property="version"
 * )
 *
 * @OA\Property(
 *     type="string",
 *     description="Refresh token hash",
 *     property="refresh_token"
 * )
 *
 * @OA\Property(
 *     type="array",
 *     description="Scopes granted for this token",
 *     property="scopes",
 *     @OA\Items(type="string")
 * )
 *
 * @OA\Property(
 *     type="string",
 *     format="date-time",
 *     description="The datetime UTC when the token expires",
 *     property="expires_on"
 * )
 *
 * @OA\Property(
 *     type="string",
 *     description="The short life access token",
 *     property="token"
 * )
 *
 * @OA\Property(
 *     type="string",
 *     format="date-time",
 *     description="The date-time when the token has been created into SeAT",
 *     property="created_at"
 * )
 *
 * @OA\Property(
 *     type="string",
 *     format="date-time",
 *     description="The date-time when the token has been updated into SeAT",
 *     property="updated_at"
 * )
 *
 * @OA\Property(
 *     type="string",
 *     format="date-time",
 *     description="The date-time when the token has been disabled",
 *     property="deleted_at"
 * )
 */
class RefreshToken extends Model
{
    use SoftDeletes;

    const CURRENT_VERSION = 2;

    /**
     * @var array
     */
    protected $attributes = [
        'version' => self::CURRENT_VERSION,
    ];

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
        'character_id', 'version', 'user_id', 'character_owner_hash', 'refresh_token', 'scopes', 'expires_on', 'token',
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
    public function affiliation()
    {
        return $this->hasOne(CharacterAffiliation::class, 'character_id', 'character_id')
            ->withDefault();
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

        return $this->belongsTo(User::class, 'user_id', 'id');
    }
}
