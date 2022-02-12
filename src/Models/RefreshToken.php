<?php

/*
 * This file is part of SeAT
 *
 * Copyright (C) 2015 to 2022 Leon Jacobs
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

class RefreshToken extends Model
{
    use SoftDeletes {
        SoftDeletes::runSoftDelete as protected traitRunSoftDelete;
    }

    const CURRENT_VERSION = 2;

    /**
     * @var \Illuminate\Contracts\Auth\Authenticatable
     */
    private static $user_instance;

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
     * @var string[]
     */
    protected $observables = [
        'softDeleted',
    ];

    /**
     * @var bool
     */
    public $incrementing = false;

    /**
     * @param  array  $attributes
     */
    public function __construct(array $attributes = [])
    {
        parent::__construct($attributes);

        // init user instance - so we can apply relationship
        if (is_null(self::$user_instance)) {
            $user_class = config('auth.providers.users.model');
            self::$user_instance = new $user_class;
        }
    }

    /**
     * Register a soft deleted model event with the dispatcher.
     *
     * @param  \Closure|string  $callback
     * @return void
     */
    public static function softDeleted($callback)
    {
        static::registerModelEvent('softDeleted', $callback);
    }

    /**
     * Only return a token value if it is not already
     * considered expired.
     *
     * @param $value
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
     */
    public function user()
    {
        return $this->belongsTo(self::$user_instance::class, 'user_id', self::$user_instance->getAuthIdentifierName());
    }

    /**
     * Perform the actual delete query on this model instance.
     *
     * @return void
     */
    protected function runSoftDelete()
    {
        // call standard soft delete workflow.
        $this->traitRunSoftDelete();

        // trigger softDeleted event.
        $this->fireModelEvent('softDeleted', false);
    }
}
