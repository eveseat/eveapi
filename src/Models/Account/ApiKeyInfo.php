<?php
/*
This file is part of SeAT

Copyright (C) 2015, 2016  Leon Jacobs

This program is free software; you can redistribute it and/or modify
it under the terms of the GNU General Public License as published by
the Free Software Foundation; either version 2 of the License, or
(at your option) any later version.

This program is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
GNU General Public License for more details.

You should have received a copy of the GNU General Public License along
with this program; if not, write to the Free Software Foundation, Inc.,
51 Franklin Street, Fifth Floor, Boston, MA 02110-1301 USA.
*/

namespace Seat\Eveapi\Models\Account;

use Illuminate\Database\Eloquent\Model;
use Seat\Eveapi\Models\Eve\ApiKey;

/**
 * Class ApiKeyInfo
 * @package Seat\Eveapi\Models
 */
class ApiKeyInfo extends Model
{

    /**
     * @var string
     */
    protected $table = 'account_api_key_infos';

    /**
     * @var string
     */
    protected $primaryKey = 'keyID';

    /**
     * @var array
     */
    protected $fillable = ['keyID', 'accessMask', 'type', 'expires'];

    /**
     * Returns the key details for this info
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasOne
     */
    public function key()
    {

        return $this->hasOne(
            ApiKey::class, 'key_id', 'keyID');
    }

    /**
     * Returns the characters for the key
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function characters()
    {

        return $this->hasMany(
            ApiKeyInfoCharacters::class, 'keyID', 'keyID');
    }
}
